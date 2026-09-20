<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import FariSelect, { type FariOption } from '@/components/fari/FariSelect.vue';
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
    brand: filters.brand,
    model: filters.model,
    year: filters.year,
    tech: filters.tech,
    q: filters.q ?? '',
    sort: filters.sort,
});

const models = computed(() =>
    form.brand ? (facets.models[form.brand] ?? []) : [],
);

const brandOptions = computed<FariOption<string>[]>(() =>
    facets.brands.map((item) => ({ value: item, label: item })),
);

const modelOptions = computed<FariOption<string>[]>(() =>
    models.value.map((item) => ({ value: item, label: item })),
);

const techOptions = computed<FariOption<string>[]>(() =>
    facets.techs.map((item) => ({ value: item, label: item })),
);

const sortOptions: FariOption<string>[] = [
    { value: 'brand', label: 'По марке' },
    { value: 'price', label: 'Сначала дешевле' },
    { value: '-price', label: 'Сначала дороже' },
];

const total = computed(() => products.total ?? products.data.length);

function apply(): void {
    router.get(
        catalogRoutes.index().url,
        {
            brand: form.brand ?? undefined,
            model: form.model ?? undefined,
            year: form.year ?? undefined,
            tech: form.tech ?? undefined,
            q: form.q || undefined,
            sort: form.sort !== 'brand' ? form.sort : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function reset(): void {
    form.brand = null;
    form.model = null;
    form.year = null;
    form.tech = null;
    form.q = '';
    form.sort = 'brand';
    apply();
}

watch(
    () => form.brand,
    () => {
        form.model = null;
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
                света. Цена указана за комплект из двух фар, заказ можно
                оформить без регистрации.
            </p>
        </div>

        <form class="toolbar" @submit.prevent="apply">
            <input
                v-model="form.q"
                class="grow"
                type="search"
                placeholder="Поиск по марке, модели, артикулу"
            />

            <FariSelect
                v-model="form.brand"
                class="filter-cell"
                :options="brandOptions"
                clearable
                placeholder="Все марки"
                search-placeholder="BMW, Audi…"
                @update:model-value="apply"
            />

            <FariSelect
                v-model="form.model"
                class="filter-cell"
                :options="modelOptions"
                :disabled="!form.brand"
                clearable
                placeholder="Все модели"
                search-placeholder="Модель или поколение"
                @update:model-value="apply"
            />

            <input
                v-model.number="form.year"
                class="year-input"
                type="number"
                min="1950"
                max="2100"
                placeholder="Год"
                @change="apply"
            />

            <FariSelect
                v-model="form.tech"
                class="filter-cell"
                :options="techOptions"
                clearable
                placeholder="Любой свет"
                @update:model-value="apply"
            />

            <FariSelect
                v-model="form.sort"
                class="filter-cell"
                :options="sortOptions"
                placeholder="По марке"
                @update:model-value="apply"
            />

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
                @add="add($event)"
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
    border: 1px solid var(--line);
    border-radius: var(--r-md);
    background: var(--surface);
    padding: 9px 12px;
}

.filter-cell {
    min-width: 190px;
}

@media (max-width: 720px) {
    .year-input,
    .filter-cell {
        width: 100%;
        min-width: 0;
    }
}
</style>
