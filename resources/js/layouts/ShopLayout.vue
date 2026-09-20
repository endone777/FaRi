<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import CartDrawer from '@/components/fari/CartDrawer.vue';
import ThemeToggle from '@/components/fari/ThemeToggle.vue';
import { useCart } from '@/composables/useCart';
import { dashboard as adminDashboard } from '@/routes/admin';
import catalog from '@/routes/catalog';
import contacts from '@/routes/contacts';
import { delivery, home } from '@/routes';
import CallbackModal from '@/components/fari/CallbackModal.vue';

const page = usePage();
const { cart } = useCart();

const drawerOpen = ref(false);
const callbackOpen = ref(false);
const menuOpen = ref(false);

const links = computed(() => [
    { label: 'Каталог', href: catalog.index(), match: '/catalog' },
    { label: 'Доставка', href: delivery(), match: '/delivery' },
    { label: 'Контакты', href: contacts.index(), match: '/contacts' },
    ...(isAdmin.value
        ? [{ label: 'Админка', href: adminDashboard(), match: '/admin' }]
        : []),
]);

function isOn(match: string): boolean {
    return page.url.split('?')[0].startsWith(match);
}

function requestCallback(): void {
    menuOpen.value = false;
    callbackOpen.value = true;
}

// Переход по ссылке закрывает меню.
watch(
    () => page.url,
    () => {
        menuOpen.value = false;
    },
);

const isAdmin = computed(() => page.props.isAdmin === true);
</script>

<template>
    <div class="fari">
        <header class="top">
            <div class="wrap top-in">
                <Link class="logo" :href="home()">
                    <span class="logo-beam" />FARI
                </Link>

                <nav class="nav">
                    <Link
                        v-for="link in links"
                        :key="link.label"
                        :href="link.href"
                    >
                        {{ link.label }}
                    </Link>
                </nav>

                <div class="top-actions top-actions-shop">
                    <button
                        class="icon-btn"
                        type="button"
                        @click="callbackOpen = true"
                    >
                        Заказать звонок
                    </button>
                    <ThemeToggle />
                    <button
                        class="icon-btn icon-btn-cart"
                        type="button"
                        :aria-label="`Корзина, товаров: ${cart.count}`"
                        @click="drawerOpen = true"
                    >
                        <span class="cart-label">Корзина</span>
                        <span class="cart-count">{{ cart.count }}</span>
                    </button>
                    <button
                        class="burger"
                        type="button"
                        aria-label="Меню"
                        :aria-expanded="menuOpen"
                        @click="menuOpen = true"
                    >
                        <span />
                        <span />
                        <span />
                    </button>
                </div>
            </div>
        </header>

        <main class="shop-main">
            <slot />
        </main>

        <footer class="foot">
            <div class="wrap">
                <div class="foot-in">
                    <div class="foot-col">
                        <b>FARI</b>
                        <span>Оптика в сборе и её компоненты</span>
                        <span>Пн–Сб, 09:00–20:00</span>
                        <Link :href="catalog.index()">Каталог</Link>
                    </div>
                    <div class="foot-col">
                        <b>Связь</b>
                        <span class="num">+7 900 000-00-00</span>
                        <span>zayavki@fari.example</span>
                        <span>Москва, ул. Автомоторная, 4с1</span>
                    </div>
                    <div class="foot-col">
                        <b>Важно</b>
                        <span>
                            Заказ оформляется без регистрации: вы оставляете
                            заявку, менеджер проверяет наличие и присылает счёт.
                        </span>
                        <span>
                            BMW, Audi, Volkswagen и Toyota — марки их
                            правообладателей, указаны как применимость.
                        </span>
                    </div>
                </div>
            </div>
        </footer>

        <div
            class="scrim"
            :class="{ on: menuOpen }"
            @click="menuOpen = false"
        />

        <nav
            class="mobile-nav"
            :class="{ on: menuOpen }"
            aria-label="Меню сайта"
        >
            <div class="mobile-nav-head">
                <Link class="logo" :href="home()" @click="menuOpen = false">
                    <span class="logo-beam" />FARI
                </Link>
                <button
                    class="x"
                    type="button"
                    aria-label="Закрыть меню"
                    @click="menuOpen = false"
                >
                    ×
                </button>
            </div>

            <div class="mobile-nav-links">
                <Link
                    v-for="link in links"
                    :key="link.label"
                    :href="link.href"
                    :class="{ on: isOn(link.match) }"
                >
                    {{ link.label }}
                </Link>
            </div>

            <div class="mobile-nav-foot">
                <button
                    class="btn btn-primary btn-wide"
                    type="button"
                    @click="requestCallback"
                >
                    Заказать звонок
                </button>
                <ThemeToggle class="btn-wide" />
            </div>
        </nav>

        <CartDrawer v-model:open="drawerOpen" />
        <CallbackModal v-model:open="callbackOpen" />
    </div>
</template>

<style scoped>
.shop-main {
    flex: 1;
}
</style>
