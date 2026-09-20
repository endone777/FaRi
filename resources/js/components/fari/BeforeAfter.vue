<script setup lang="ts">
import { computed, ref } from 'vue';
import HeadlightThumb from '@/components/fari/HeadlightThumb.vue';
import type { Product } from '@/types/fari';

const { product = null, fallbackBefore = '/images/catalog/old1.jpg' } =
    defineProps<{
        product?: Product | null;
        fallbackBefore?: string;
    }>();

const position = ref(50);

const before = computed(() => product?.photo_old ?? fallbackBefore);
const after = computed(() => product?.photo ?? null);

const hint = computed(() => {
    if (!product) {
        return 'Выберите фару — покажем её на месте старой';
    }

    if (!after.value) {
        return 'Фотография не загружена — показываем схему. Добавьте фото в админке.';
    }

    return `Было: помутневшая фара. Стало: ${product.brand} ${product.model}, ${product.name}`;
});
</script>

<template>
    <div>
        <div class="ba" :style="{ '--pos': `${position}%` }">
            <img class="ba-img" :src="before" alt="Фара до замены" />
            <div class="ba-clip">
                <img
                    v-if="after"
                    class="ba-img"
                    :src="after"
                    alt="Фара после замены"
                />
                <div v-else class="ba-schema">
                    <HeadlightThumb
                        v-if="product"
                        :shape="product.shape"
                        :color-temp="product.color_temp"
                    />
                </div>
            </div>
            <input
                v-model.number="position"
                class="ba-range"
                type="range"
                min="0"
                max="100"
                aria-label="Сравнение до и после"
            />
            <span class="ba-line" />
            <span class="ba-grip" aria-hidden="true">⇄</span>
            <span class="ba-tag ba-tag-l">Было</span>
            <span class="ba-tag ba-tag-r">Стало</span>
        </div>
        <p class="ba-hint">{{ hint }}</p>
    </div>
</template>

<style scoped>
.ba-schema {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, var(--stage-1), var(--stage-2));
}
</style>
