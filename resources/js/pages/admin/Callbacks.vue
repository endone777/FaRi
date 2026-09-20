<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import Pagination from '@/components/fari/Pagination.vue';
import adminRoutes from '@/routes/admin';
import type { Paginated } from '@/types/fari';

type Callback = {
    id: number;
    name: string;
    phone: string;
    preferred_time: string | null;
    status: string;
    created_at: string;
};

const { callbacks, statuses } = defineProps<{
    callbacks: Paginated<Callback>;
    statuses: Record<string, string>;
}>();

function setStatus(callback: Callback, status: string): void {
    router.patch(
        adminRoutes.callbacks.update(callback.id).url,
        { status },
        { preserveScroll: true },
    );
}

function destroy(callback: Callback): void {
    if (!confirm(`Удалить запись от ${callback.name}?`)) {
        return;
    }

    router.delete(adminRoutes.callbacks.destroy(callback.id).url, {
        preserveScroll: true,
    });
}

function formatDate(value: string): string {
    return new Date(value).toLocaleString('ru-RU');
}
</script>

<template>
    <Head title="Админка — звонки" />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow">Заявки на звонок</span>
            <h2>Обратный звонок</h2>
        </div>
        <p>Кнопка «Заказать звонок» в шапке витрины пишет сюда.</p>
    </div>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Когда удобно</th>
                    <th>Создана</th>
                    <th>Статус</th>
                    <th />
                </tr>
            </thead>
            <tbody>
                <tr v-if="callbacks.data.length === 0">
                    <td colspan="6">
                        <div class="empty">Заявок нет</div>
                    </td>
                </tr>

                <tr v-for="item in callbacks.data" :key="item.id">
                    <td class="row-name">{{ item.name }}</td>
                    <td class="num">{{ item.phone }}</td>
                    <td class="row-sub">{{ item.preferred_time || '—' }}</td>
                    <td class="row-sub">{{ formatDate(item.created_at) }}</td>
                    <td>
                        <select
                            :value="item.status"
                            @change="
                                setStatus(
                                    item,
                                    ($event.target as HTMLSelectElement).value,
                                )
                            "
                        >
                            <option
                                v-for="(label, value) in statuses"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </td>
                    <td>
                        <div class="row-acts">
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

    <Pagination :links="callbacks.links" />
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}
</style>
