<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import HeadlightThumb from '@/components/fari/HeadlightThumb.vue';
import { money } from '@/lib/fari';
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
</script>

<template>
    <article class="card">
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
        </button>

        <div class="card-body">
            <div class="card-head">
                <h3 class="card-title">
                    <Link
                        class="title-btn"
                        :href="`/catalog/${product.slug}`"
                    >
                        {{ product.brand }} {{ product.model }}
                    </Link>
                </h3>
                <span
                    class="pill"
                    :class="product.in_stock ? 'pill-ok' : 'pill-wait'"
                >
                    {{
                        product.in_stock
                            ? `в наличии ${product.qty} шт.`
                            : 'под заказ'
                    }}
                </span>
            </div>

            <p class="card-fit">{{ product.name }}</p>

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
                <button
                    v-if="product.in_stock"
                    class="btn btn-primary"
                    type="button"
                    @click="emit('add', product)"
                >
                    В корзину
                </button>
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
