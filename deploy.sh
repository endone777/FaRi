#!/bin/sh
#
# Поднимает FARI на сервере одной командой.
#
#   git clone <repo> && cd fari-lara && ./deploy.sh
#
# Скрипт на POSIX sh: работает и через ./deploy.sh, и через `sh deploy.sh`,
# и в bash, и в busybox. Если на сервере нет Docker, предложит установить его
# официальным установщиком get.docker.com. Повторный запуск после `git pull`
# пересобирает образ и накатывает миграции, не теряя данных.

set -eu

cd "$(dirname "$0")"

COMPOSE_FILE="compose.prod.yaml"
DB_PATH="/var/www/html/storage/app/database/database.sqlite"
DOCKER_INSTALLER_URL="https://get.docker.com"

APP_PORT="${APP_PORT:-80}"
APP_URL_OVERRIDE=""
SEED="false"
ACTION="up"
NO_CACHE=""
INSTALL_DOCKER="ask"
DOCKER_PREFIX=""
COMPOSE_CMD=""

# ── вывод ────────────────────────────────────────────────

if [ -t 1 ]; then
    BOLD=$(printf '\033[1m')
    GREEN=$(printf '\033[32m')
    YELLOW=$(printf '\033[33m')
    RED=$(printf '\033[31m')
    OFF=$(printf '\033[0m')
else
    BOLD=""; GREEN=""; YELLOW=""; RED=""; OFF=""
fi

step() { printf '%s==>%s %s\n' "$BOLD" "$OFF" "$1"; }
ok()   { printf '%s  ✓%s %s\n' "$GREEN" "$OFF" "$1"; }
warn() { printf '%s  !%s %s\n' "$YELLOW" "$OFF" "$1"; }
die()  { printf '%s  ✗%s %s\n' "$RED" "$OFF" "$1" >&2; exit 1; }

usage() {
    cat <<'USAGE'
FARI — развёртывание в Docker.

Использование: ./deploy.sh [команда] [опции]

Команды:
  (без команды)       собрать образ и поднять приложение
  down                остановить приложение (данные остаются)
  logs                показать логи контейнера
  status              показать состояние контейнера
  shell               открыть оболочку внутри контейнера

Опции:
  --port N            порт на хосте (по умолчанию 80)
  --url ADDRESS       публичный адрес, например https://fari.ru
  --seed              наполнить базу справочником автомобилей и каталогом
                      (выполняется автоматически при первом запуске)
  --rebuild           собрать образ заново, без кэша слоёв
  --install-docker    поставить Docker без лишних вопросов
  --no-install-docker не ставить Docker, просто сообщить об его отсутствии
  -h, --help          эта справка

Примеры:
  ./deploy.sh                                     # первый запуск на 80 порту
  sudo ./deploy.sh --install-docker               # чистый сервер без Docker
  ./deploy.sh --port 8080 --url http://1.2.3.4:8080
  ./deploy.sh down
USAGE
}

# ── разбор аргументов ────────────────────────────────────

while [ $# -gt 0 ]; do
    case "$1" in
        up|down|logs|status|shell) ACTION="$1"; shift ;;
        --port) [ $# -ge 2 ] || die "--port требует значение"; APP_PORT="$2"; shift 2 ;;
        --port=*) APP_PORT="${1#*=}"; shift ;;
        --url) [ $# -ge 2 ] || die "--url требует значение"; APP_URL_OVERRIDE="$2"; shift 2 ;;
        --url=*) APP_URL_OVERRIDE="${1#*=}"; shift ;;
        --seed) SEED="true"; shift ;;
        --rebuild) NO_CACHE="--no-cache"; shift ;;
        --install-docker) INSTALL_DOCKER="yes"; shift ;;
        --no-install-docker) INSTALL_DOCKER="no"; shift ;;
        -h|--help) usage; exit 0 ;;
        *) die "Неизвестный аргумент: $1 (см. ./deploy.sh --help)" ;;
    esac
done

case "$APP_PORT" in
    ''|*[!0-9]*) die "Порт должен быть числом, получено: $APP_PORT" ;;
esac

have() { command -v "$1" >/dev/null 2>&1; }

# ── права администратора ─────────────────────────────────

SUDO=""

if [ "$(id -u)" -ne 0 ] && have sudo; then
    SUDO="sudo"
fi

as_root() {
    if [ "$(id -u)" -eq 0 ]; then
        "$@"
    elif [ -n "$SUDO" ]; then
        "$SUDO" "$@"
    else
        die "Нужны права root: запустите скрипт через sudo."
    fi
}

# ── базовые утилиты ──────────────────────────────────────

pkg_install() {
    step "Ставлю $*"

    if have apt-get; then
        as_root env DEBIAN_FRONTEND=noninteractive apt-get update -qq
        as_root env DEBIAN_FRONTEND=noninteractive apt-get install -y -qq "$@"
    elif have dnf; then
        as_root dnf install -y -q "$@"
    elif have yum; then
        as_root yum install -y -q "$@"
    elif have zypper; then
        as_root zypper --non-interactive install "$@"
    elif have apk; then
        as_root apk add --no-cache "$@"
    elif have pacman; then
        as_root pacman -Sy --noconfirm "$@"
    else
        die "Не знаю пакетный менеджер этой системы. Установите вручную: $*"
    fi
}

if ! have curl && ! have wget; then
    pkg_install curl
fi

fetch() {
    # fetch <адрес> <файл>
    if have curl; then
        curl -fsSL "$1" -o "$2"
    else
        wget -qO "$2" "$1"
    fi
}

http_ok() {
    if have curl; then
        curl -fsS --max-time 3 "$1" >/dev/null 2>&1
    else
        wget -q --timeout=3 --tries=1 -O /dev/null "$1" >/dev/null 2>&1
    fi
}

# ── Docker ───────────────────────────────────────────────

docker_cli() {
    if [ -n "$DOCKER_PREFIX" ]; then
        "$DOCKER_PREFIX" docker "$@"
    else
        docker "$@"
    fi
}

compose() {
    if [ "$COMPOSE_CMD" = "plugin" ]; then
        docker_cli compose -f "$COMPOSE_FILE" "$@"
    elif [ -n "$DOCKER_PREFIX" ]; then
        "$DOCKER_PREFIX" docker-compose -f "$COMPOSE_FILE" "$@"
    else
        docker-compose -f "$COMPOSE_FILE" "$@"
    fi
}

install_docker() {
    if [ "$INSTALL_DOCKER" = "no" ]; then
        die "Docker не установлен. Поставьте его: https://docs.docker.com/engine/install/"
    fi

    if [ "$INSTALL_DOCKER" = "ask" ]; then
        printf '\n'
        warn "Docker на сервере не найден."
        printf '    Скрипт может установить его официальным установщиком %s\n' "$DOCKER_INSTALLER_URL"
        printf '    (его же рекомендует документация Docker). Нужны права root.\n\n'

        if [ -t 0 ]; then
            printf '    Установить Docker сейчас? [Y/n] '
            read -r answer || answer=""

            case "$answer" in
                [Nn]*) die "Хорошо. Установите Docker вручную и запустите скрипт снова." ;;
            esac
        else
            die "Docker не установлен. Запустите с флагом --install-docker, чтобы поставить его."
        fi
    fi

    if [ "$(id -u)" -ne 0 ] && [ -z "$SUDO" ]; then
        die "Для установки Docker нужны права root: sudo ./deploy.sh --install-docker"
    fi

    step "Ставлю Docker Engine и плагин Compose"

    installer=$(mktemp)
    trap 'rm -f "$installer"' EXIT INT TERM

    fetch "$DOCKER_INSTALLER_URL" "$installer" \
        || die "Не удалось скачать установщик Docker. Проверьте доступ в интернет."

    as_root sh "$installer" || die "Установщик Docker завершился с ошибкой."

    rm -f "$installer"
    trap - EXIT INT TERM

    have docker || die "Docker не появился после установки. Поставьте его вручную."

    ok "Docker установлен"

    if have systemctl; then
        as_root systemctl enable --now docker >/dev/null 2>&1 || true
    elif have rc-update; then
        as_root rc-update add docker default >/dev/null 2>&1 || true
        as_root rc-service docker start >/dev/null 2>&1 || true
    elif have service; then
        as_root service docker start >/dev/null 2>&1 || true
    fi

    if [ "$(id -u)" -ne 0 ] && [ -n "${USER:-}" ] && have usermod; then
        as_root usermod -aG docker "$USER" >/dev/null 2>&1 || true
        warn "Пользователь $USER добавлен в группу docker."
        warn "Чтобы работать с docker без sudo, перезайдите в систему."
    fi
}

start_docker_daemon() {
    step "Запускаю демон Docker"

    if have systemctl; then
        as_root systemctl enable --now docker >/dev/null 2>&1 || true
    elif have rc-service; then
        as_root rc-service docker start >/dev/null 2>&1 || true
    elif have service; then
        as_root service docker start >/dev/null 2>&1 || true
    fi

    waited=0

    while [ "$waited" -lt 15 ]; do
        if docker info >/dev/null 2>&1; then
            return 0
        fi

        if [ -n "$SUDO" ] && "$SUDO" docker info >/dev/null 2>&1; then
            return 0
        fi

        waited=$((waited + 1))
        sleep 1
    done

    return 1
}

have docker || install_docker

if ! docker info >/dev/null 2>&1; then
    if [ -n "$SUDO" ] && "$SUDO" docker info >/dev/null 2>&1; then
        DOCKER_PREFIX="$SUDO"
    else
        start_docker_daemon \
            || die "Демон Docker не отвечает. Запустите его: sudo systemctl start docker"

        if ! docker info >/dev/null 2>&1; then
            DOCKER_PREFIX="$SUDO"
        fi
    fi
fi

if docker_cli compose version >/dev/null 2>&1; then
    COMPOSE_CMD="plugin"
elif have docker-compose; then
    COMPOSE_CMD="standalone"
else
    warn "Нет плагина Docker Compose — ставлю его."
    pkg_install docker-compose-plugin

    docker_cli compose version >/dev/null 2>&1 \
        || die "Docker Compose так и не заработал: https://docs.docker.com/compose/install/"

    COMPOSE_CMD="plugin"
fi

[ -f "$COMPOSE_FILE" ] || die "Рядом со скриптом нет $COMPOSE_FILE — запускайте из корня проекта."

# ── вспомогательные команды ──────────────────────────────

case "$ACTION" in
    down)
        step "Останавливаю приложение"
        compose down
        ok "Остановлено. Данные сохранены в томе fari-storage."
        exit 0
        ;;
    logs) compose logs -f --tail=200 app; exit 0 ;;
    status) compose ps; exit 0 ;;
    shell) compose exec app sh; exit 0 ;;
esac

# ── .env ─────────────────────────────────────────────────

set_env() {
    key="$1"
    value="$2"
    escaped=$(printf '%s' "$value" | sed -e 's/[\/&|]/\\&/g')

    if grep -q "^#\{0,1\}${key}=" .env; then
        sed -i "s|^#\{0,1\}${key}=.*|${key}=${escaped}|" .env
    else
        printf '%s=%s\n' "$key" "$value" >> .env
    fi
}

read_env() {
    sed -n "s/^$1=//p" .env | tail -n 1
}

FIRST_RUN="false"

if [ ! -f .env ]; then
    [ -f .env.example ] || die "Нет ни .env, ни .env.example — репозиторий склонирован не полностью."
    step "Создаю .env из .env.example"
    cp .env.example .env
    FIRST_RUN="true"
    ok ".env создан"
fi

APP_KEY_VALUE=$(read_env APP_KEY)

if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" = "base64:" ]; then
    step "Генерирую ключ приложения"

    if have openssl; then
        KEY="base64:$(openssl rand -base64 32)"
    else
        KEY="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
    fi

    set_env APP_KEY "$KEY"
    ok "APP_KEY записан в .env"
fi

if [ -n "$APP_URL_OVERRIDE" ]; then
    APP_URL_VALUE="$APP_URL_OVERRIDE"
elif [ "$APP_PORT" = "80" ]; then
    HOST_IP=$(hostname -I 2>/dev/null | awk '{print $1}' || true)

    if [ -z "$HOST_IP" ]; then
        HOST_IP=$(hostname -i 2>/dev/null | awk '{print $1}' || true)
    fi

    case "$HOST_IP" in
        ''|127.*) HOST_IP="localhost" ;;
    esac

    APP_URL_VALUE="http://${HOST_IP}"
else
    APP_URL_VALUE="http://localhost:${APP_PORT}"
fi

step "Настраиваю .env под продакшен"
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "$APP_URL_VALUE"
set_env APP_PORT "$APP_PORT"
set_env DB_CONNECTION sqlite
set_env DB_DATABASE "$DB_PATH"
ok "APP_URL=$APP_URL_VALUE, порт $APP_PORT"

# Первый запуск наполняет каталог, дальше — только по флагу --seed.
if [ "$FIRST_RUN" = "true" ]; then
    SEED="true"
fi

# ── занятость порта ──────────────────────────────────────

if have ss && ss -ltn "sport = :$APP_PORT" 2>/dev/null | grep -q LISTEN; then
    if ! compose ps --services --filter status=running 2>/dev/null | grep -q '^app$'; then
        warn "Порт $APP_PORT уже занят другим процессом — запуск может не удаться."
        warn "Освободите его или укажите другой: ./deploy.sh --port 8080"
    fi
fi

# ── сборка и запуск ──────────────────────────────────────

step "Собираю образ (первый раз это занимает несколько минут)"

build_image() {
    if [ -n "$NO_CACHE" ]; then
        compose build "$NO_CACHE"
    else
        compose build
    fi
}

# Сборка тянет базовые образы из реестра, а связь с ним бывает нестабильной.
attempt=1

until build_image; do
    if [ "$attempt" -ge 3 ]; then
        printf '\n'
        warn "Сборка не удалась три раза подряд."
        warn "Обычно причина — сеть: сервер не достучался до registry-1.docker.io."
        warn "Проверьте вручную: docker pull php:8.4-fpm-alpine"
        warn "Если реестр недоступен, пропишите зеркало в /etc/docker/daemon.json:"
        warn '  {"registry-mirrors": ["https://mirror.gcr.io"]}'
        warn "и перезапустите Docker: sudo systemctl restart docker"
        die "Сборка образа не завершилась."
    fi

    attempt=$((attempt + 1))
    warn "Сборка сорвалась, пробую ещё раз ($attempt из 3)…"
    sleep 5
done

step "Запускаю приложение"
DEPLOY_SEED="$SEED" compose up -d --remove-orphans

step "Жду, пока приложение ответит"

READY="false"
waited=0

while [ "$waited" -lt 60 ]; do
    if http_ok "http://127.0.0.1:${APP_PORT}/up"; then
        READY="true"
        break
    fi

    if ! compose ps --services --filter status=running 2>/dev/null | grep -q '^app$'; then
        break
    fi

    waited=$((waited + 1))
    sleep 2
done

if [ "$READY" != "true" ]; then
    printf '\n'
    warn "Приложение не ответило за две минуты. Последние строки лога:"
    printf '\n'
    compose logs --tail=40 app
    die "Запуск не завершился. Смотрите лог: ./deploy.sh logs"
fi

printf '\n'
ok "FARI работает: ${BOLD}${APP_URL_VALUE}${OFF}"
ok "Админка: ${APP_URL_VALUE}/admin"

if [ "$SEED" = "true" ]; then
    ok "Вход в админку: admin@fari.test / password"
    warn "Смените пароль администратора сразу после первого входа."
fi

printf '\n'
printf 'Полезное:\n'
printf '  ./deploy.sh logs      логи\n'
printf '  ./deploy.sh status    состояние\n'
printf '  ./deploy.sh shell     оболочка в контейнере\n'
printf '  ./deploy.sh down      остановить\n'
