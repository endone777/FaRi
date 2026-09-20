<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import HeadlightThumb from '@/components/fari/HeadlightThumb.vue';
import { useCart } from '@/composables/useCart';
import { money } from '@/lib/fari';
import catalog from '@/routes/catalog';
import cartRoutes from '@/routes/cart';
import type { Product } from '@/types/fari';

const {
    product,
    fits = false,
    showFitButton = true,
} = defineProps<{
    product: Product;
    fits?: boolean;
    showFitButton?: boolean;
}>();

const emit = defineEmits<{
    fit: [product: Product];
    add: [product: Product];
    notify: [product: Product];
}>();

const { qtyOf } = useCart();
</script>

<template>
    <article class="card" :class="{ 'card-in-cart': qtyOf(product) > 0 }">
        <button
            class="card-vis"
            type="button"
            :title="
                showFitButton ? 'Примерить на схеме' : 'Открыть карточку товара'
            "
            @click="emit('fit', product)"
        >
            <img
                v-if="product.photo"
                class="card-photo"
                :src="product.photo"
                :alt="`${product.brand} ${product.model}`"
                loading="lazy"
            />
            <HeadlightThumb
                v-else
                :shape="product.shape"
                :color-temp="product.color_temp"
                :label="product.title"
            />
            <span v-if="fits" class="badge">подходит вашей</span>
            <span v-if="qtyOf(product) > 0" class="badge badge-cart">
                в корзине · {{ qtyOf(product) }}
            </span>
        </button>

        <div class="card-body">
            <div class="card-head">
                <h3 class="card-title">
                    <Link class="title-btn" :href="catalog.show(product.slug)">
                        {{ product.brand }} {{ product.model }}
                    </Link>
                </h3>
                <span
                    class="pill"
                    :class="product.in_stock ? 'pill-ok' : 'pill-wait'"
                >
                    {{
                        product.in_stock
                            ? `в наличии ${product.qty} компл.`
                            : 'под заказ'
                    }}
                </span>
            </div>

            <p class="card-fit">{{ product.name }} · комплект из двух фар</p>

            <dl class="spec">
                <dt>Источник</dt>
                <dd>{{ product.tech }}</dd>
                <dt>Температура</dt>
                <dd>{{ product.color_temp }}K</dd>
                <dt>Годы</dt>
                <dd>{{ product.year_from }}–{{ product.year_to }}</dd>
                <dt>Артикул</dt>
                <dd>{{ product.oem }}</dd>
            </dl>

            <div class="card-buy">
                <span class="price">{{ money(product.price) }}</span>

                <template v-if="product.in_stock">
                    <div v-if="qtyOf(product) > 0" class="card-in-cart-actions">
                        <Link
                            class="btn btn-primary"
                            :href="cartRoutes.index()"
                        >
                            В корзине · {{ qtyOf(product) }}
                        </Link>
                        <button
                            class="btn btn-sm"
                            type="button"
                            @click="emit('add', product)"
                        >
                            Ещё комплект
                        </button>
                    </div>
                    <button
                        v-else
                        class="btn btn-primary"
                        type="button"
                        @click="emit('add', product)"
                    >
                        В корзину
                    </button>
                </template>

                <button
                    v-else
                    class="btn"
                    type="button"
                    @click="emit('notify', product)"
                >
                    Уведомить
                </button>
            </div>
        </div>
    </article>
</template>

<style scoped>
.card-in-cart {
    border-color: var(--xenon);
    box-shadow:
        0 0 0 1px var(--xenon) inset,
        var(--shadow);
}

.badge-cart {
    inset-block-start: auto;
    inset-block-end: 10px;
    background: var(--xenon);
    color: var(--on-accent);
    border-color: var(--xenon);
}

.card-in-cart-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
}
</style>
