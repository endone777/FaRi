<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import HeadlightThumb from '@/components/fari/HeadlightThumb.vue';
import ProductCard from '@/components/fari/ProductCard.vue';
import { useCart } from '@/composables/useCart';
import { money, SHAPE_NAMES } from '@/lib/fari';
import cartRoutes from '@/routes/cart';
import catalog from '@/routes/catalog';
import checkout from '@/routes/checkout';
import contacts from '@/routes/contacts';
import type { DeliveryMethod, Product } from '@/types/fari';

const { product, related, deliveryMethods } = defineProps<{
    product: Product;
    related: Product[];
    deliveryMethods: DeliveryMethod[];
}>();

const { add, qtyOf } = useCart();

const tab = ref<'desc' | 'spec' | 'ship'>('desc');
const shot = ref(0);

const shots = computed(() => {
    const list: { key: string; label: string; src: string | null }[] = [];

    if (product.photo) {
        list.push({ key: 'new', label: 'Новая', src: product.photo });
    }

    list.push({
        key: 'old',
        label: 'Старая',
        src: product.photo_old ?? '/images/catalog/old1.jpg',
    });

    list.push({ key: 'svg', label: 'Схема', src: null });

    return list;
});

const active = computed(() => shots.value[shot.value] ?? shots.value[0]);

function buyNow(): void {
    add(product);
    router.visit(checkout.create().url);
}
</script>

<template>
    <Head :title="product.title" />

    <section class="section wrap">
        <nav class="crumbs">
            <Link :href="catalog.index()">Каталог</Link>
            <span>/</span>
            <Link :href="catalog.index({ query: { brand: product.brand } })">
                {{ product.brand }}
            </Link>
            <span>/</span>
            <span class="crumbs-now">{{ product.model }}</span>
        </nav>

        <div class="product-grid">
            <div class="product-vis">
                <div class="modal-shot">
                    <img
                        v-if="active.src"
                        :src="active.src"
                        :alt="product.title"
                    />
                    <HeadlightThumb
                        v-else
                        :shape="product.shape"
                        :color-temp="product.color_temp"
                        :label="product.title"
                    />
                </div>

                <div class="thumbs">
                    <button
                        v-for="(item, index) in shots"
                        :key="item.key"
                        type="button"
                        :title="item.label"
                        :aria-pressed="shot === index"
                        @click="shot = index"
                    >
                        <img
                            v-if="item.src"
                            :src="item.src"
                            :alt="item.label"
                        />
                        <HeadlightThumb
                            v-else
                            :shape="product.shape"
                            :color-temp="product.color_temp"
                        />
                    </button>
                </div>
            </div>

            <div class="modal-info product-info">
                <div class="product-badges">
                    <span
                        class="pill"
                        :class="product.in_stock ? 'pill-ok' : 'pill-wait'"
                    >
                        {{
                            product.in_stock
                                ? `в наличии ${product.qty} компл.`
                                : product.stock_note || 'под заказ'
                        }}
                    </span>
                    <span class="opt-meta">Артикул: {{ product.oem }}</span>
                </div>

                <h1>{{ product.brand }} {{ product.model }}</h1>
                <p class="modal-sub">
                    {{ product.name }} · комплект · {{ product.tech }} ·
                    {{ product.color_temp }}K · {{ product.year_from }}–{{
                        product.year_to
                    }}
                </p>

                <div class="modal-price">
                    <span class="price">{{ money(product.price) }}</span>
                    <span class="opt-meta">
                        гарантия {{ product.warranty_months }} мес.
                    </span>
                </div>

                <p class="set-note">
                    Продаётся комплектом: левая и правая фара в одной коробке.
                </p>

                <div class="modal-buy">
                    <template v-if="product.in_stock">
                        <button
                            class="btn btn-primary"
                            type="button"
                            @click="add(product)"
                        >
                            {{
                                qtyOf(product) > 0
                                    ? 'Добавить ещё комплект'
                                    : 'В корзину'
                            }}
                        </button>
                        <Link
                            v-if="qtyOf(product) > 0"
                            class="btn"
                            :href="cartRoutes.index()"
                        >
                            В корзине · {{ qtyOf(product) }}
                        </Link>
                        <button
                            v-else
                            class="btn"
                            type="button"
                            @click="buyNow"
                        >
                            Купить сейчас
                        </button>
                    </template>
                    <Link
                        v-else
                        class="btn btn-primary"
                        :href="contacts.index()"
                    >
                        Уведомить о поступлении
                    </Link>
                </div>

                <div class="tabs" role="tablist">
                    <button
                        class="tab-btn"
                        type="button"
                        role="tab"
                        :aria-selected="tab === 'desc'"
                        @click="tab = 'desc'"
                    >
                        Описание
                    </button>
                    <button
                        class="tab-btn"
                        type="button"
                        role="tab"
                        :aria-selected="tab === 'spec'"
                        @click="tab = 'spec'"
                    >
                        Характеристики
                    </button>
                    <button
                        class="tab-btn"
                        type="button"
                        role="tab"
                        :aria-selected="tab === 'ship'"
                        @click="tab = 'ship'"
                    >
                        Доставка
                    </button>
                </div>

                <div v-if="tab === 'desc'" class="tab-panel on">
                    <p>
                        {{
                            product.description || 'Описание пока не заполнено.'
                        }}
                    </p>
                </div>

                <div v-else-if="tab === 'spec'" class="tab-panel on">
                    <ul class="mini-list">
                        <li>
                            <span>Марка и модель</span>
                            <b>{{ product.brand }} {{ product.model }}</b>
                        </li>
                        <li>
                            <span>Годы выпуска</span>
                            <b>
                                {{ product.year_from }}–{{ product.year_to }}
                            </b>
                        </li>
                        <li>
                            <span>Источник света</span>
                            <b>{{ product.tech }}</b>
                        </li>
                        <li>
                            <span>Цветовая температура</span>
                            <b>{{ product.color_temp }} K</b>
                        </li>
                        <li>
                            <span>Форма корпуса</span>
                            <b>{{ SHAPE_NAMES[product.shape] ?? '—' }}</b>
                        </li>
                        <li>
                            <span>Вес с упаковкой</span>
                            <b>{{ product.weight }} кг</b>
                        </li>
                        <li>
                            <span>Артикул</span>
                            <b>{{ product.oem }}</b>
                        </li>
                        <li>
                            <span>Гарантия</span>
                            <b>{{ product.warranty_months }} мес.</b>
                        </li>
                    </ul>
                </div>

                <div v-else class="tab-panel on">
                    <ul class="mini-list">
                        <li v-for="method in deliveryMethods" :key="method.id">
                            <span>{{ method.name }}</span>
                            <b>
                                {{
                                    method.cost === 0
                                        ? 'бесплатно'
                                        : money(method.cost)
                                }}
                                · {{ method.days }}
                            </b>
                        </li>
                    </ul>
                    <p>
                        Вес этой позиции — {{ product.weight }} кг. В регионы
                        уходит объёмным местом 60×40×35 см, поэтому транспортная
                        компания считает по габаритам, а не по весу.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section v-if="related.length > 0" class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Та же марка</span>
                <h2>Смотрят ещё</h2>
            </div>
        </div>

        <div class="grid">
            <ProductCard
                v-for="item in related"
                :key="item.id"
                :product="item"
                :show-fit-button="false"
                @fit="router.visit(catalog.show(item.slug).url)"
                @add="add($event)"
                @notify="router.visit(contacts.index().url)"
            />
        </div>
    </section>
</template>

<style scoped>
.crumbs {
    display: flex;
    gap: 8px;
    align-items: center;
    font-size: var(--step--1);
    color: var(--text-mute);
    margin-bottom: 18px;
}

.crumbs-now {
    color: var(--text);
}

.set-note {
    font-size: var(--step--1);
    color: var(--text-dim);
}

.product-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 24px;
    align-items: start;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.product-vis {
    display: grid;
    gap: 12px;
    padding: 18px;
    background: var(--surface-2);
    border-inline-end: 1px solid var(--line);
}

.product-info h1 {
    font-size: var(--step-3);
    text-transform: uppercase;
}

.product-badges {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.section-title {
    display: grid;
    gap: 8px;
}

@media (max-width: 860px) {
    .product-grid {
        grid-template-columns: 1fr;
    }

    .product-vis {
        border-inline-end: 0;
        border-block-end: 1px solid var(--line);
    }
}
</style>
