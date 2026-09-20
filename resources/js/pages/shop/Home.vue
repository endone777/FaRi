<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BeforeAfter from '@/components/fari/BeforeAfter.vue';
import CarStage from '@/components/fari/CarStage.vue';
import ProductCard from '@/components/fari/ProductCard.vue';
import { useCart } from '@/composables/useCart';
import { beamColor, money, SIDES } from '@/lib/fari';
import catalog from '@/routes/catalog';
import contacts from '@/routes/contacts';
import { delivery } from '@/routes';
import type { DeliveryMethod, Product } from '@/types/fari';

const { products, deliveryMethods } = defineProps<{
    products: Product[];
    deliveryMethods: DeliveryMethod[];
    topics: string[];
}>();

const { add } = useCart();

const brand = ref('');
const model = ref('');
const year = ref('');
const tech = ref('');
const side = ref('left');
const fitted = ref<Product | null>(null);

const brands = computed(() => [
    ...new Set(products.map((product) => product.brand)),
]);

const models = computed(() =>
    brand.value
        ? [
              ...new Set(
                  products
                      .filter((product) => product.brand === brand.value)
                      .map((product) => product.model),
              ),
          ]
        : [],
);

const years = computed(() => {
    if (!brand.value || !model.value) {
        return [];
    }

    const found = new Set<number>();

    products
        .filter(
            (product) =>
                product.brand === brand.value && product.model === model.value,
        )
        .forEach((product) => {
            for (let y = product.year_from; y <= product.year_to; y++) {
                found.add(y);
            }
        });

    return [...found].sort((a, b) => b - a);
});

const fitting = computed(() =>
    products.filter((product) => {
        if (brand.value && product.brand !== brand.value) {
            return false;
        }

        if (model.value && product.model !== model.value) {
            return false;
        }

        if (tech.value && product.tech !== tech.value) {
            return false;
        }

        if (year.value) {
            const value = Number(year.value);

            if (value < product.year_from || value > product.year_to) {
                return false;
            }
        }

        return true;
    }),
);

const featured = computed(() => products.slice(0, 6));

const stageTitle = computed(() =>
    brand.value
        ? `${brand.value} ${model.value || '—'}${year.value ? `, ${year.value}` : ''}`
        : 'Выберите автомобиль',
);

function onBrandChange(): void {
    model.value = '';
    year.value = '';
    fitted.value = null;
}

function onModelChange(): void {
    year.value = '';
}

function search(): void {
    router.get(catalog.index().url, {
        brand: brand.value || undefined,
        model: model.value || undefined,
        year: year.value || undefined,
        tech: tech.value || undefined,
    });
}

function fit(product: Product): void {
    fitted.value = product;

    if (!brand.value) {
        brand.value = product.brand;
        model.value = product.model;
    }

    document
        .getElementById('fit')
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<template>
    <Head title="Автомобильная оптика" />

    <section class="hero">
        <div class="wrap hero-in">
            <div class="hero-copy">
                <span class="eyebrow">
                    Оптика в сборе · Ксенон · LED · Галоген
                </span>
                <h1>Свет, который <em>встаёт</em> на вашу машину</h1>
                <p class="hero-lead">
                    Выберите марку, модель и год — каталог оставит только
                    подходящее. Нажмите на фару: она встанет на место в схеме
                    вашего автомобиля, а слайдер покажет, как было и как станет.
                    Заказ оформляется без регистрации.
                </p>
            </div>

            <div class="hero-marks">
                <span v-for="item in brands" :key="item" class="mark-chip">
                    {{ item }}
                </span>
            </div>

            <div class="picker">
                <div class="picker-cell">
                    <label for="sel-brand">Марка</label>
                    <select
                        id="sel-brand"
                        v-model="brand"
                        @change="onBrandChange"
                    >
                        <option value="">Выберите</option>
                        <option v-for="item in brands" :key="item">
                            {{ item }}
                        </option>
                    </select>
                </div>

                <div class="picker-cell">
                    <label for="sel-model">Модель</label>
                    <select
                        id="sel-model"
                        v-model="model"
                        :disabled="!brand"
                        @change="onModelChange"
                    >
                        <option value="">—</option>
                        <option v-for="item in models" :key="item">
                            {{ item }}
                        </option>
                    </select>
                </div>

                <div class="picker-cell">
                    <label for="sel-year">Год выпуска</label>
                    <select id="sel-year" v-model="year" :disabled="!model">
                        <option value="">—</option>
                        <option v-for="item in years" :key="item">
                            {{ item }}
                        </option>
                    </select>
                </div>

                <div class="picker-cell">
                    <label for="sel-tech">Тип света</label>
                    <select id="sel-tech" v-model="tech">
                        <option value="">Любой</option>
                        <option>LED</option>
                        <option>Ксенон</option>
                        <option>Галоген</option>
                    </select>
                </div>

                <button class="picker-go" type="button" @click="search">
                    Подобрать
                </button>
            </div>
        </div>
    </section>

    <section id="fit" class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Примерка</span>
                <h2>Фара встаёт на место</h2>
            </div>
            <p>
                Слева — фара, которая стоит сейчас: помутневшая, с запотеванием.
                Справа — та, что приедет к вам. Потяните ползунок.
            </p>
        </div>

        <div class="fit">
            <div class="fit-visuals">
                <BeforeAfter :product="fitted" />

                <div>
                    <div class="stage">
                        <div class="stage-label">
                            <span class="eyebrow">Схема установки</span>
                            <strong>{{ stageTitle }}</strong>
                        </div>
                        <CarStage
                            :brand="brand || 'BMW'"
                            :fitted="fitted"
                            :side="side"
                        />
                    </div>

                    <div class="stage-foot">
                        <span>
                            Позиция:
                            <b>{{ fitted ? SIDES[side].toLowerCase() : '—' }}</b>
                        </span>
                        <span>Источник: <b>{{ fitted?.tech ?? '—' }}</b></span>
                        <span>
                            Температура:
                            <b>
                                {{ fitted ? `${fitted.color_temp}K` : '—' }}
                            </b>
                        </span>
                        <span>Артикул: <b>{{ fitted?.oem ?? '—' }}</b></span>
                    </div>
                </div>
            </div>

            <div class="fit-list">
                <div class="fit-list-head">
                    <h3>Подходит</h3>
                    <div
                        class="side-toggle"
                        role="group"
                        aria-label="Сторона установки"
                    >
                        <button
                            v-for="(label, value) in SIDES"
                            :key="value"
                            type="button"
                            :aria-pressed="side === value"
                            @click="side = value"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>

                <div>
                    <div v-if="!brand" class="empty">
                        Выберите марку в подборщике — покажем, что подходит.
                    </div>
                    <div v-else-if="fitting.length === 0" class="empty">
                        Под этот набор параметров ничего нет.<br />
                        Оставьте заявку — найдём под заказ.
                    </div>
                    <button
                        v-for="item in fitting"
                        v-else
                        :key="item.id"
                        class="opt"
                        type="button"
                        :aria-pressed="fitted?.id === item.id"
                        @click="fitted = item"
                    >
                        <span
                            class="opt-swatch"
                            :style="{ background: beamColor(item.color_temp) }"
                        />
                        <span>
                            <span class="opt-name">
                                {{ item.model }} · {{ item.name }}
                            </span>
                            <br />
                            <span class="opt-meta">
                                {{ item.tech }} · {{ item.color_temp }}K ·
                                {{ item.year_from }}–{{ item.year_to }}
                            </span>
                        </span>
                        <span class="opt-price">{{ money(item.price) }}</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">
                    Каталог · {{ products.length }} позиций
                </span>
                <h2>Популярная оптика</h2>
            </div>
            <p>
                Нажмите на фотографию — фара встанет в схему вашей машины.
                Нажмите на название — откроется карточка с описанием,
                характеристиками и доставкой.
            </p>
        </div>

        <div class="grid">
            <ProductCard
                v-for="item in featured"
                :key="item.id"
                :product="item"
                :fits="brand === item.brand"
                @fit="fit"
                @add="add($event, side)"
                @notify="router.visit(contacts.index().url)"
            />
        </div>

        <div class="section-cta">
            <Link class="btn btn-primary" :href="catalog.index()">
                Открыть весь каталог
            </Link>
        </div>
    </section>

    <section class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Доставка и самовывоз</span>
                <h2>Как фара доедет</h2>
            </div>
            <p>
                Фара — хрупкий и объёмный груз. Пакуем в жёсткий короб с
                пенопластовым ложементом и снимаем видео упаковки.
            </p>
        </div>

        <div class="ship-grid">
            <article
                v-for="method in deliveryMethods"
                :key="method.id"
                class="ship"
            >
                <h4>{{ method.name }}</h4>
                <div class="ship-meta">
                    <span class="num">
                        {{ method.cost === 0 ? 'бесплатно' : money(method.cost) }}
                    </span>
                    <span>{{ method.days }}</span>
                </div>
                <p>{{ method.note }}</p>
            </article>
        </div>

        <div class="section-cta">
            <Link class="btn" :href="delivery()">
                Условия доставки и возврата
            </Link>
            <Link class="btn" :href="contacts.index()">Задать вопрос</Link>
        </div>
    </section>
</template>

<style scoped>
.hero-copy,
.section-title {
    display: grid;
    gap: 8px;
}

.hero-copy {
    gap: 18px;
}

.fit-visuals {
    display: grid;
    gap: 18px;
}

.section-cta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 24px;
}
</style>
