<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Pagination from '@/components/fari/Pagination.vue';
import { money } from '@/lib/fari';
import adminRoutes from '@/routes/admin';
import type { Order, Paginated } from '@/types/fari';

const { orders, filters, statuses } = defineProps<{
    orders: Paginated<Order>;
    filters: { q: string | null; status: string | null };
    statuses: Record<string, string>;
}>();

const form = reactive({
    q: filters.q ?? '',
    status: filters.status ?? '',
});

function apply(): void {
    router.get(
        adminRoutes.orders.index().url,
        { q: form.q || undefined, status: form.status || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleString('ru-RU') : '—';
}
</script>

<template>
    <Head title="Админка — заявки" />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow">
                Заявки · {{ orders.total ?? orders.data.length }}
            </span>
            <h2>Заказы с витрины</h2>
        </div>
        <p>
            Заявки приходят без регистрации: клиент оставляет контакты, вы
            подтверждаете наличие и выставляете счёт.
        </p>
    </div>

    <form class="toolbar" @submit.prevent="apply">
        <input
            v-model="form.q"
            class="grow"
            type="search"
            placeholder="Номер, имя, телефон или почта"
        />
        <select v-model="form.status" @change="apply">
            <option value="">Все статусы</option>
            <option
                v-for="(label, value) in statuses"
                :key="value"
                :value="value"
            >
                {{ label }}
            </option>
        </select>
        <button class="btn btn-primary" type="submit">Найти</button>
    </form>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Номер</th>
                    <th>Клиент</th>
                    <th>Доставка</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Создана</th>
                    <th />
                </tr>
            </thead>
            <tbody>
                <tr v-if="orders.data.length === 0">
                    <td colspan="7">
                        <div class="empty">Заявок нет</div>
                    </td>
                </tr>

                <tr v-for="order in orders.data" :key="order.id">
                    <td class="row-sub">{{ order.number }}</td>
                    <td>
                        <div class="row-name">{{ order.customer_name }}</div>
                        <div class="row-sub">
                            {{ order.phone }} · {{ order.email }}
                        </div>
                    </td>
                    <td class="row-sub">
                        {{ order.delivery_name }}<br />{{ order.city }}
                    </td>
                    <td class="num">{{ money(order.total) }}</td>
                    <td>
                        <span
                            class="pill"
                            :class="
                                order.status === 'new' ? 'pill-wait' : 'pill-ok'
                            "
                        >
                            {{ order.status_label }}
                        </span>
                    </td>
                    <td class="row-sub">{{ formatDate(order.created_at) }}</td>
                    <td>
                        <div class="row-acts">
                            <Link
                                class="btn btn-sm"
                                :href="adminRoutes.orders.show(order.number)"
                            >
                                Открыть
                            </Link>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :links="orders.links" />
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}
</style>
