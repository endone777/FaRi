<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import FariSelect, { type FariOption } from '@/components/fari/FariSelect.vue';
import HeadlightThumb from '@/components/fari/HeadlightThumb.vue';
import adminRoutes from '@/routes/admin';
import type { CarBrandOption, CarModelOption, Product } from '@/types/fari';

const { product, options } = defineProps<{
    product: Product | null;
    options: {
        techs: string[];
        shapes: Record<string, string>;
        cars: CarBrandOption[];
    };
}>();

const isEdit = computed(() => product !== null);

const brandId = ref<number | null>(
    options.cars.find((brand) =>
        brand.models.some((model) => model.id === product?.car_model_id),
    )?.id ?? null,
);

const brandOptions = computed<FariOption<number>[]>(() =>
    options.cars.map((brand) => ({
        value: brand.id,
        label: brand.name,
        hint: `${brand.models.length} мод.`,
    })),
);

const modelOptions = computed<FariOption<number>[]>(() => {
    const brand = options.cars.find((item) => item.id === brandId.value);

    return (brand?.models ?? []).map((model: CarModelOption) => ({
        value: model.id,
        label: model.name,
        hint: model.years,
    }));
});

const form = useForm({
    _method: product ? 'put' : 'post',
    car_model_id: product?.car_model_id ?? null,
    name: product?.name ?? '',
    year_from: product?.year_from ?? new Date().getFullYear() - 5,
    year_to: product?.year_to ?? new Date().getFullYear(),
    tech: product?.tech ?? options.techs[0],
    color_temp: product?.color_temp ?? 5000,
    shape: product?.shape ?? 'sharp',
    oem: product?.oem ?? '',
    price: product?.price ?? 0,
    qty: product?.qty ?? 0,
    stock_note: product?.stock_note ?? 'на складе',
    weight: product?.weight ?? 0,
    warranty_months: product?.warranty_months ?? 12,
    description: product?.description ?? '',
    is_active: product?.is_active ?? true,
    photo: null as File | null,
    photo_old: null as File | null,
    remove_photo: false,
    remove_photo_old: false,
});

/** Picking a make drops a model that belongs to another one. */
watch(brandId, () => {
    if (
        !modelOptions.value.some((option) => option.value === form.car_model_id)
    ) {
        form.car_model_id = null;
    }
});

/** A freshly picked generation suggests its own production years. */
watch(
    () => form.car_model_id,
    (value) => {
        const brand = options.cars.find((item) => item.id === brandId.value);
        const model = brand?.models.find((item) => item.id === value);

        if (model && !isEdit.value) {
            form.year_from = model.year_from;
            form.year_to = model.year_to;
        }
    },
);

const photoPreview = ref<string | null>(product?.photo ?? null);
const photoOldPreview = ref<string | null>(product?.photo_old ?? null);

function pickPhoto(event: Event, which: 'photo' | 'photo_old'): void {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    const url = file ? URL.createObjectURL(file) : null;

    if (which === 'photo') {
        form.photo = file;
        form.remove_photo = false;
        photoPreview.value = url ?? product?.photo ?? null;
    } else {
        form.photo_old = file;
        form.remove_photo_old = false;
        photoOldPreview.value = url ?? product?.photo_old ?? null;
    }
}

function clearPhoto(which: 'photo' | 'photo_old'): void {
    if (which === 'photo') {
        form.photo = null;
        form.remove_photo = true;
        photoPreview.value = null;
    } else {
        form.photo_old = null;
        form.remove_photo_old = true;
        photoOldPreview.value = null;
    }
}

function submit(): void {
    if (product) {
        form.post(adminRoutes.products.update(product.id).url, {
            forceFormData: true,
        });

        return;
    }

    form.post(adminRoutes.products.store().url, { forceFormData: true });
}
</script>

<template>
    <Head
        :title="isEdit ? 'Админка — правка позиции' : 'Админка — новая позиция'"
    />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow">Каталог</span>
            <h2>{{ isEdit ? 'Правка позиции' : 'Новая фара' }}</h2>
        </div>
        <p>
            Поля отмечают, как позиция выглядит на витрине: фотографии, свет,
            остаток и срок поставки.
        </p>
    </div>

    <div class="admin-grid">
        <form class="panel admin-form" novalidate @submit.prevent="submit">
            <h3>Карточка</h3>

            <div class="admin-preview">
                <img
                    v-if="photoPreview"
                    :src="photoPreview"
                    alt="Предпросмотр"
                />
                <HeadlightThumb
                    v-else
                    :shape="form.shape"
                    :color-temp="Number(form.color_temp)"
                />
            </div>

            <div class="row2">
                <div class="field">
                    <label for="brand">Марка</label>
                    <FariSelect
                        id="brand"
                        v-model="brandId"
                        :options="brandOptions"
                        placeholder="Выберите марку"
                        search-placeholder="BMW, Audi…"
                        empty-text="Такой марки нет в справочнике"
                    />
                </div>
                <div class="field">
                    <label for="car_model_id">Модель</label>
                    <FariSelect
                        id="car_model_id"
                        v-model="form.car_model_id"
                        :options="modelOptions"
                        :disabled="brandId === null"
                        :invalid="Boolean(form.errors.car_model_id)"
                        placeholder="Сначала выберите марку"
                        search-placeholder="3 Series, Golf…"
                        empty-text="Такой модели нет в справочнике"
                    />
                    <span v-if="form.errors.car_model_id" class="hint hint-bad">
                        {{ form.errors.car_model_id }}
                    </span>
                    <span v-else class="hint">
                        Марки и модели берутся из
                        <Link :href="adminRoutes.cars.index()"
                            >справочника</Link
                        >
                        автомобилей.
                    </span>
                </div>
            </div>

            <div class="field">
                <label for="name">Название позиции</label>
                <input
                    id="name"
                    v-model="form.name"
                    required
                    placeholder="Adaptive LED в сборе"
                />
                <span v-if="form.errors.name" class="hint hint-bad">
                    {{ form.errors.name }}
                </span>
            </div>

            <div class="row2">
                <div class="field">
                    <label for="year_from">Год с</label>
                    <input
                        id="year_from"
                        v-model.number="form.year_from"
                        class="num"
                        type="number"
                        min="1950"
                        max="2100"
                    />
                    <span v-if="form.errors.year_from" class="hint hint-bad">
                        {{ form.errors.year_from }}
                    </span>
                </div>
                <div class="field">
                    <label for="year_to">Год по</label>
                    <input
                        id="year_to"
                        v-model.number="form.year_to"
                        class="num"
                        type="number"
                        min="1950"
                        max="2100"
                    />
                    <span v-if="form.errors.year_to" class="hint hint-bad">
                        {{ form.errors.year_to }}
                    </span>
                </div>
            </div>

            <div class="row2">
                <div class="field">
                    <label for="tech">Источник света</label>
                    <select id="tech" v-model="form.tech">
                        <option v-for="tech in options.techs" :key="tech">
                            {{ tech }}
                        </option>
                    </select>
                </div>
                <div class="field">
                    <label for="color_temp">Температура, K</label>
                    <input
                        id="color_temp"
                        v-model.number="form.color_temp"
                        class="num"
                        type="number"
                        min="2000"
                        max="8000"
                        step="100"
                    />
                </div>
            </div>

            <div class="field">
                <label>Форма корпуса</label>
                <div class="shape-pick">
                    <label
                        v-for="(label, value) in options.shapes"
                        :key="value"
                        :class="{ on: form.shape === value }"
                    >
                        <input
                            v-model="form.shape"
                            type="radio"
                            name="shape"
                            :value="value"
                        />
                        {{ label }}
                    </label>
                </div>
            </div>

            <div class="row2">
                <div class="field">
                    <label for="price">Цена, ₽</label>
                    <input
                        id="price"
                        v-model.number="form.price"
                        class="num"
                        type="number"
                        min="0"
                        step="100"
                    />
                    <span v-if="form.errors.price" class="hint hint-bad">
                        {{ form.errors.price }}
                    </span>
                </div>
                <div class="field">
                    <label for="qty">Остаток, шт.</label>
                    <input
                        id="qty"
                        v-model.number="form.qty"
                        class="num"
                        type="number"
                        min="0"
                    />
                    <span v-if="form.errors.qty" class="hint hint-bad">
                        {{ form.errors.qty }}
                    </span>
                </div>
            </div>

            <div class="row2">
                <div class="field">
                    <label for="weight">Вес, кг</label>
                    <input
                        id="weight"
                        v-model.number="form.weight"
                        class="num"
                        type="number"
                        min="0"
                        step="0.1"
                    />
                </div>
                <div class="field">
                    <label for="warranty_months">Гарантия, мес.</label>
                    <input
                        id="warranty_months"
                        v-model.number="form.warranty_months"
                        class="num"
                        type="number"
                        min="0"
                    />
                </div>
            </div>

            <div class="field">
                <label for="oem">Артикул</label>
                <input id="oem" v-model="form.oem" required />
                <span v-if="form.errors.oem" class="hint hint-bad">
                    {{ form.errors.oem }}
                </span>
            </div>

            <div class="field">
                <label for="stock_note">Срок поставки</label>
                <input
                    id="stock_note"
                    v-model="form.stock_note"
                    placeholder="на складе / под заказ, 5–7 дней"
                />
            </div>

            <div class="field">
                <label for="description">Описание</label>
                <textarea
                    id="description"
                    v-model="form.description"
                    placeholder="Что с корпусом, стеклом, начинкой; что входит в комплект"
                />
            </div>

            <div class="row2">
                <div class="field">
                    <label>Фото «стало»</label>
                    <div class="drop">
                        <img
                            v-if="photoPreview"
                            :src="photoPreview"
                            alt="Фото стало"
                        />
                        <div v-else class="drop-empty">нет фото</div>
                        <input
                            type="file"
                            accept="image/*"
                            class="file-input"
                            @change="pickPhoto($event, 'photo')"
                        />
                        <button
                            class="btn btn-sm"
                            type="button"
                            @click="clearPhoto('photo')"
                        >
                            Убрать
                        </button>
                    </div>
                    <span v-if="form.errors.photo" class="hint hint-bad">
                        {{ form.errors.photo }}
                    </span>
                </div>

                <div class="field">
                    <label>Фото «было»</label>
                    <div class="drop">
                        <img
                            v-if="photoOldPreview"
                            :src="photoOldPreview"
                            alt="Фото было"
                        />
                        <div v-else class="drop-empty">нет фото</div>
                        <input
                            type="file"
                            accept="image/*"
                            class="file-input"
                            @change="pickPhoto($event, 'photo_old')"
                        />
                        <button
                            class="btn btn-sm"
                            type="button"
                            @click="clearPhoto('photo_old')"
                        >
                            Убрать
                        </button>
                    </div>
                    <span v-if="form.errors.photo_old" class="hint hint-bad">
                        {{ form.errors.photo_old }}
                    </span>
                </div>
            </div>

            <label class="switch">
                <input v-model="form.is_active" type="checkbox" />
                <span>Показывать на витрине</span>
            </label>

            <div class="form-actions">
                <button
                    class="btn btn-primary"
                    type="submit"
                    :disabled="form.processing"
                >
                    {{ isEdit ? 'Сохранить' : 'Добавить фару' }}
                </button>
                <Link class="btn" :href="adminRoutes.products.index()">
                    Отмена
                </Link>
            </div>

            <div v-if="form.progress" class="hint">
                Загрузка: {{ form.progress.percentage }}%
            </div>
        </form>

        <div class="panel">
            <h3>Подсказки</h3>
            <ul class="mini-list">
                <li><span>Фото «стало»</span><b>новая фара, 16:9</b></li>
                <li>
                    <span>Фото «было»</span><b>помутневшая, для слайдера</b>
                </li>
                <li>
                    <span>Остаток 0</span><b>витрина покажет «под заказ»</b>
                </li>
                <li>
                    <span>Форма корпуса</span><b>силуэт на схеме установки</b>
                </li>
                <li><span>Температура</span><b>задаёт цвет луча</b></li>
            </ul>
            <p class="note">
                Без фотографии карточка показывает схематичную фару — она
                строится из формы корпуса и цветовой температуры.
            </p>
        </div>
    </div>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.file-input {
    font-size: 0.72rem;
}

.hint-bad {
    color: var(--bad);
}

.switch {
    display: flex;
    gap: 8px;
    align-items: center;
    font-size: var(--step--1);
}

.form-actions {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
}
</style>
