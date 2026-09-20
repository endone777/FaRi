<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import Pagination from '@/components/fari/Pagination.vue';
import ProductCard from '@/components/fari/ProductCard.vue';
import { useCart } from '@/composables/useCart';
import catalogRoutes from '@/routes/catalog';
import contacts from '@/routes/contacts';
import type { Paginated, Product } from '@/types/fari';

const { products, filters, facets } = defineProps<{
    products: Paginated<Product>;
    filters: {
        brand: string | null;
        model: string | null;
        year: number | null;
        tech: string | null;
        q: string | null;
        sort: string;
    };
    facets: {
        brands: string[];
        models: Record<string, string[]>;
        techs: string[];
    };
}>();

const { add } = useCart();

const form = reactive({
    brand: filters.brand ?? '',
    model: filters.model ?? '',
    year: filters.year ? String(filters.year) : '',
    tech: filters.tech ?? '',
    q: filters.q ?? '',
    sort: filters.sort,
});

const models = computed(() =>
    form.brand ? (facets.models[form.brand] ?? []) : [],
);

const total = computed(() => products.total ?? products.data.length);

function apply(): void {
    router.get(
        catalogRoutes.index().url,
        {
            brand: form.brand || undefined,
            model: form.model || undefined,
            year: form.year || undefined,
            tech: form.tech || undefined,
            q: form.q || undefined,
            sort: form.sort !== 'brand' ? form.sort : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function reset(): void {
    form.brand = '';
    form.model = '';
    form.year = '';
    form.tech = '';
    form.q = '';
    form.sort = 'brand';
    apply();
}

watch(
    () => form.brand,
    () => {
        form.model = '';
    },
);

function openProduct(product: Product): void {
    router.visit(catalogRoutes.show(product.slug).url);
}
</script>

<template>
    <Head title="Каталог оптики" />

    <section class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Каталог · {{ total }} позиций</span>
                <h2>Вся оптика</h2>
            </div>
            <p>
                Фильтры работают по марке, модели, году выпуска и источнику
                света. Заказ можно оформить без регистрации.
            </p>
        </div>

        <form class="toolbar" @submit.prevent="apply">
            <input
                v-model="form.q"
                class="grow"
                type="search"
                placeholder="Поиск по марке, модели, артикулу"
            />

            <select v-model="form.brand" @change="apply">
                <option value="">Все марки</option>
                <option v-for="brand in facets.brands" :key="brand">
                    {{ brand }}
                </option>
            </select>

            <select v-model="form.model" :disabled="!form.brand" @change="apply">
                <option value="">Все модели</option>
                <option v-for="model in models" :key="model">{{ model }}</option>
            </select>

            <input
                v-model="form.year"
                class="year-input"
                type="number"
                min="1950"
                max="2100"
                placeholder="Год"
                @change="apply"
            />

            <select v-model="form.tech" @change="apply">
                <option value="">Любой свет</option>
                <option v-for="tech in facets.techs" :key="tech">
                    {{ tech }}
                </option>
            </select>

            <select v-model="form.sort" @change="apply">
                <option value="brand">По марке</option>
                <option value="price">Сначала дешевле</option>
                <option value="-price">Сначала дороже</option>
            </select>

            <button class="btn btn-primary" type="submit">Найти</button>
            <button class="btn" type="button" @click="reset">Сбросить</button>
        </form>

        <div v-if="products.data.length === 0" class="empty">
            Под эти параметры ничего не нашлось.<br />
            Напишите нам — поищем под заказ.
        </div>

        <div v-else class="grid">
            <ProductCard
                v-for="item in products.data"
                :key="item.id"
                :product="item"
                :show-fit-button="false"
                @fit="openProduct"
                @add="add($event, 'left')"
                @notify="router.visit(contacts.index().url)"
            />
        </div>

        <Pagination :links="products.links" />
    </section>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.year-input {
    width: 110px;
}
</style>
