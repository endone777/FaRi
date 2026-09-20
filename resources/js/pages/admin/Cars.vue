<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import FariSelect, { type FariOption } from '@/components/fari/FariSelect.vue';
import adminRoutes from '@/routes/admin';

type DirectoryModel = {
    id: number;
    car_brand_id: number;
    name: string;
    year_from: number;
    year_to: number | null;
    years: string;
    products_count: number;
};

type DirectoryBrand = {
    id: number;
    name: string;
    position: number;
    models_count: number;
    models: DirectoryModel[];
};

const { brands, filters, earliestYear, currentYear } = defineProps<{
    brands: DirectoryBrand[];
    filters: { q: string | null };
    earliestYear: number;
    currentYear: number;
}>();

const search = reactive({ q: filters.q ?? '' });

const editingBrand = ref<DirectoryBrand | null>(null);
const editingModel = ref<DirectoryModel | null>(null);
const openBrand = ref<number | null>(brands[0]?.id ?? null);

const brandForm = useForm({ name: '', position: 0 });
const modelForm = useForm({
    car_brand_id: null as number | null,
    name: '',
    year_from: earliestYear,
    year_to: null as number | null,
});

const brandOptions = computed<FariOption<number>[]>(() =>
    brands.map((brand) => ({
        value: brand.id,
        label: brand.name,
        hint: `${brand.models_count} мод.`,
    })),
);

const totalModels = computed(() =>
    brands.reduce((sum, brand) => sum + brand.models_count, 0),
);

function apply(): void {
    router.get(
        adminRoutes.cars.index().url,
        { q: search.q || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function editBrand(brand: DirectoryBrand): void {
    editingBrand.value = brand;
    brandForm.clearErrors();
    brandForm.name = brand.name;
    brandForm.position = brand.position;
}

function resetBrand(): void {
    editingBrand.value = null;
    brandForm.clearErrors();
    brandForm.name = '';
    brandForm.position = brands.length + 1;
}

function submitBrand(): void {
    const done = { preserveScroll: true, onSuccess: resetBrand };

    if (editingBrand.value) {
        brandForm.patch(
            adminRoutes.cars.brands.update(editingBrand.value.id).url,
            done,
        );

        return;
    }

    brandForm.post(adminRoutes.cars.brands.store().url, done);
}

function destroyBrand(brand: DirectoryBrand): void {
    if (!confirm(`Удалить марку «${brand.name}» со всеми моделями?`)) {
        return;
    }

    router.delete(adminRoutes.cars.brands.destroy(brand.id).url, {
        preserveScroll: true,
    });
}

function newModel(brandId: number): void {
    editingModel.value = null;
    modelForm.clearErrors();
    modelForm.car_brand_id = brandId;
    modelForm.name = '';
    modelForm.year_from = earliestYear;
    modelForm.year_to = null;
}

function editModel(model: DirectoryModel): void {
    editingModel.value = model;
    modelForm.clearErrors();
    modelForm.car_brand_id = model.car_brand_id;
    modelForm.name = model.name;
    modelForm.year_from = model.year_from;
    modelForm.year_to = model.year_to;
}

function resetModel(): void {
    editingModel.value = null;
    modelForm.clearErrors();
    modelForm.name = '';
    modelForm.year_from = earliestYear;
    modelForm.year_to = null;
}

function submitModel(): void {
    const done = { preserveScroll: true, onSuccess: resetModel };

    if (editingModel.value) {
        modelForm.patch(
            adminRoutes.cars.models.update(editingModel.value.id).url,
            done,
        );

        return;
    }

    modelForm.post(adminRoutes.cars.models.store().url, done);
}

function destroyModel(model: DirectoryModel): void {
    if (!confirm(`Удалить модель «${model.name}»?`)) {
        return;
    }

    router.delete(adminRoutes.cars.models.destroy(model.id).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Админка — справочник автомобилей" />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow">
                Справочник · {{ brands.length }} марок ·
                {{ totalModels }} моделей
            </span>
            <h2>Автомобили</h2>
        </div>
        <p>
            Поколения, выпускавшиеся с {{ earliestYear }} года. Из этого
            справочника берутся марка и модель при заведении фары, поэтому
            опечатки в каталог не попадают.
        </p>
    </div>

    <form class="toolbar" @submit.prevent="apply">
        <input
            v-model="search.q"
            class="grow"
            type="search"
            placeholder="Поиск по модели"
        />
        <button class="btn btn-primary" type="submit">Найти</button>
    </form>

    <div class="admin-grid">
        <div class="cars-forms">
            <form class="panel" novalidate @submit.prevent="submitBrand">
                <h3>{{ editingBrand ? 'Правка марки' : 'Новая марка' }}</h3>

                <div class="field">
                    <label for="brand-name">Название</label>
                    <input
                        id="brand-name"
                        v-model="brandForm.name"
                        placeholder="BMW"
                    />
                    <span v-if="brandForm.errors.name" class="hint hint-bad">
                        {{ brandForm.errors.name }}
                    </span>
                </div>

                <div class="field">
                    <label for="brand-position">Порядок в списке</label>
                    <input
                        id="brand-position"
                        v-model.number="brandForm.position"
                        class="num"
                        type="number"
                        min="0"
                    />
                </div>

                <div class="form-actions">
                    <button
                        class="btn btn-primary"
                        type="submit"
                        :disabled="brandForm.processing"
                    >
                        {{ editingBrand ? 'Сохранить' : 'Добавить марку' }}
                    </button>
                    <button class="btn" type="button" @click="resetBrand">
                        Очистить
                    </button>
                </div>
            </form>

            <form class="panel" novalidate @submit.prevent="submitModel">
                <h3>{{ editingModel ? 'Правка модели' : 'Новая модель' }}</h3>

                <div class="field">
                    <label for="model-brand">Марка</label>
                    <FariSelect
                        id="model-brand"
                        v-model="modelForm.car_brand_id"
                        :options="brandOptions"
                        :invalid="Boolean(modelForm.errors.car_brand_id)"
                        placeholder="Выберите марку"
                    />
                    <span
                        v-if="modelForm.errors.car_brand_id"
                        class="hint hint-bad"
                    >
                        {{ modelForm.errors.car_brand_id }}
                    </span>
                </div>

                <div class="field">
                    <label for="model-name">Модель и поколение</label>
                    <input
                        id="model-name"
                        v-model="modelForm.name"
                        placeholder="3 Series (G20)"
                    />
                    <span v-if="modelForm.errors.name" class="hint hint-bad">
                        {{ modelForm.errors.name }}
                    </span>
                </div>

                <div class="row2">
                    <div class="field">
                        <label for="model-from">Выпуск с</label>
                        <input
                            id="model-from"
                            v-model.number="modelForm.year_from"
                            class="num"
                            type="number"
                            :min="earliestYear"
                            :max="currentYear + 1"
                        />
                        <span
                            v-if="modelForm.errors.year_from"
                            class="hint hint-bad"
                        >
                            {{ modelForm.errors.year_from }}
                        </span>
                    </div>
                    <div class="field">
                        <label for="model-to">Выпуск по</label>
                        <input
                            id="model-to"
                            v-model.number="modelForm.year_to"
                            class="num"
                            type="number"
                            :min="earliestYear"
                            :max="currentYear + 1"
                            placeholder="пусто — выпускается"
                        />
                        <span
                            v-if="modelForm.errors.year_to"
                            class="hint hint-bad"
                        >
                            {{ modelForm.errors.year_to }}
                        </span>
                    </div>
                </div>

                <div class="form-actions">
                    <button
                        class="btn btn-primary"
                        type="submit"
                        :disabled="modelForm.processing"
                    >
                        {{ editingModel ? 'Сохранить' : 'Добавить модель' }}
                    </button>
                    <button class="btn" type="button" @click="resetModel">
                        Очистить
                    </button>
                </div>
            </form>
        </div>

        <div class="flow">
            <section v-for="brand in brands" :key="brand.id" class="panel">
                <div class="brand-head">
                    <button
                        class="brand-toggle"
                        type="button"
                        :aria-expanded="openBrand === brand.id"
                        @click="
                            openBrand = openBrand === brand.id ? null : brand.id
                        "
                    >
                        <span class="brand-name">{{ brand.name }}</span>
                        <span class="row-sub">
                            {{ brand.models_count }} моделей
                        </span>
                    </button>

                    <div class="row-acts">
                        <button
                            class="btn btn-sm"
                            type="button"
                            @click="newModel(brand.id)"
                        >
                            + модель
                        </button>
                        <button
                            class="btn btn-sm"
                            type="button"
                            @click="editBrand(brand)"
                        >
                            Изменить
                        </button>
                        <button
                            class="btn btn-sm btn-danger"
                            type="button"
                            @click="destroyBrand(brand)"
                        >
                            Удалить
                        </button>
                    </div>
                </div>

                <div v-if="openBrand === brand.id" class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Модель</th>
                                <th>Годы выпуска</th>
                                <th>Позиций</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="brand.models.length === 0">
                                <td colspan="4">
                                    <div class="empty">Моделей нет</div>
                                </td>
                            </tr>

                            <tr
                                v-for="model in brand.models"
                                :key="model.id"
                                :class="{
                                    editing: editingModel?.id === model.id,
                                }"
                            >
                                <td class="row-name">{{ model.name }}</td>
                                <td class="row-sub num">{{ model.years }}</td>
                                <td>
                                    <span
                                        class="pill"
                                        :class="
                                            model.products_count > 0
                                                ? 'pill-ok'
                                                : 'pill-wait'
                                        "
                                    >
                                        {{ model.products_count }}
                                    </span>
                                </td>
                                <td>
                                    <div class="row-acts">
                                        <button
                                            class="btn btn-sm"
                                            type="button"
                                            @click="editModel(model)"
                                        >
                                            Изменить
                                        </button>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            type="button"
                                            @click="destroyModel(model)"
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.cars-forms {
    display: grid;
    gap: 20px;
    align-content: start;
    position: sticky;
    top: 82px;
}

.brand-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.brand-toggle {
    display: flex;
    align-items: baseline;
    gap: 10px;
    border: 0;
    background: transparent;
    color: inherit;
    cursor: pointer;
    padding: 0;
}

.brand-name {
    font-family: var(--f-display);
    font-weight: 800;
    font-size: var(--step-1);
    text-transform: uppercase;
}

.hint-bad {
    color: var(--bad);
}

.form-actions {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
}

@media (max-width: 900px) {
    .cars-forms {
        position: static;
    }
}
</style>
