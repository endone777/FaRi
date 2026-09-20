<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Pagination from '@/components/fari/Pagination.vue';
import { money } from '@/lib/fari';
import adminRoutes from '@/routes/admin';
import type { Paginated, Product } from '@/types/fari';

const { products, filters, brands } = defineProps<{
    products: Paginated<Product>;
    filters: { q: string | null; brand: string | null; state: string | null };
    brands: string[];
}>();

const form = reactive({
    q: filters.q ?? '',
    brand: filters.brand ?? '',
    state: filters.state ?? '',
});

function apply(): void {
    router.get(
        adminRoutes.products.index().url,
        {
            q: form.q || undefined,
            brand: form.brand || undefined,
            state: form.state || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function destroy(product: Product): void {
    if (!confirm(`Удалить «${product.title}»? Действие необратимо.`)) {
        return;
    }

    router.delete(adminRoutes.products.destroy(product.id).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Админка — каталог" />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow">
                Каталог · {{ products.total ?? products.data.length }} позиций
            </span>
            <h2>Управление фарами</h2>
        </div>
        <p>
            Добавляйте позиции, загружайте фотографии «было» и «стало». Всё, что
            вы сохраните, сразу появляется на витрине.
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
            <option v-for="brand in brands" :key="brand">{{ brand }}</option>
        </select>
        <select v-model="form.state" @change="apply">
            <option value="">Все позиции</option>
            <option value="hidden">Скрытые</option>
            <option value="out">Под заказ</option>
        </select>
        <button class="btn btn-primary" type="submit">Найти</button>
        <Link class="btn btn-primary" :href="adminRoutes.products.create()">
            Добавить фару
        </Link>
    </form>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Фото</th>
                    <th>Товар</th>
                    <th>Свет</th>
                    <th>Цена</th>
                    <th>Остаток</th>
                    <th>Витрина</th>
                    <th />
                </tr>
            </thead>
            <tbody>
                <tr v-if="products.data.length === 0">
                    <td colspan="7">
                        <div class="empty">Ничего не найдено</div>
                    </td>
                </tr>

                <tr v-for="item in products.data" :key="item.id">
                    <td>
                        <img
                            v-if="item.photo"
                            class="row-thumb"
                            :src="item.photo"
                            alt=""
                        />
                        <span v-else class="row-thumb row-thumb-empty">
                            схема
                        </span>
                    </td>
                    <td>
                        <div class="row-name">
                            {{ item.brand }} {{ item.model }}
                        </div>
                        <div class="row-sub">
                            {{ item.name }} · {{ item.oem }}
                        </div>
                    </td>
                    <td>
                        <div class="row-sub">{{ item.tech }}</div>
                        <div class="row-sub">{{ item.color_temp }}K</div>
                    </td>
                    <td class="num">{{ money(item.price) }}</td>
                    <td>
                        <span
                            class="pill"
                            :class="item.in_stock ? 'pill-ok' : 'pill-wait'"
                        >
                            {{ item.in_stock ? `${item.qty} шт.` : 'заказ' }}
                        </span>
                    </td>
                    <td>
                        <span
                            class="pill"
                            :class="item.is_active ? 'pill-ok' : 'pill-wait'"
                        >
                            {{ item.is_active ? 'видна' : 'скрыта' }}
                        </span>
                    </td>
                    <td>
                        <div class="row-acts">
                            <Link
                                class="btn btn-sm"
                                :href="adminRoutes.products.edit(item.id)"
                            >
                                Изменить
                            </Link>
                            <button
                                class="btn btn-sm btn-danger"
                                type="button"
                                @click="destroy(item)"
                            >
                                Удалить
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :links="products.links" />
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.row-thumb-empty {
    display: grid;
    place-items: center;
    font-size: 0.6rem;
    color: var(--text-mute);
}
</style>
