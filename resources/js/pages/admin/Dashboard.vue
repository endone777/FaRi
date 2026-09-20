<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { money } from '@/lib/fari';
import adminRoutes from '@/routes/admin';
import type { Order } from '@/types/fari';

const { stats, recentOrders, lowStock } = defineProps<{
    stats: {
        products: number;
        productsHidden: number;
        outOfStock: number;
        ordersNew: number;
        ordersTotal: number;
        revenue: number;
        callbacksNew: number;
        messagesNew: number;
    };
    recentOrders: Order[];
    lowStock: {
        id: number;
        slug: string;
        brand: string;
        model: string;
        name: string;
        qty: number;
    }[];
}>();
</script>

<template>
    <Head title="Админка — сводка" />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow">Админка</span>
            <h2>Сводка</h2>
        </div>
        <p>
            Всё, что происходит на витрине: заявки, звонки, сообщения и остатки
            по складу.
        </p>
    </div>

    <div class="stat-grid">
        <div class="stat">
            <b>{{ stats.ordersNew }}</b>
            <span>новых заявок</span>
        </div>
        <div class="stat">
            <b>{{ stats.ordersTotal }}</b>
            <span>заявок всего</span>
        </div>
        <div class="stat">
            <b class="stat-money">{{ money(stats.revenue) }}</b>
            <span>сумма подтверждённых заявок</span>
        </div>
        <div class="stat">
            <b>{{ stats.products }}</b>
            <span>позиций в каталоге</span>
        </div>
        <div class="stat">
            <b>{{ stats.outOfStock }}</b>
            <span>позиций под заказ</span>
        </div>
        <div class="stat">
            <b>{{ stats.productsHidden }}</b>
            <span>скрыто с витрины</span>
        </div>
        <div class="stat">
            <b>{{ stats.callbacksNew }}</b>
            <span>заявок на звонок</span>
        </div>
        <div class="stat">
            <b>{{ stats.messagesNew }}</b>
            <span>новых сообщений</span>
        </div>
    </div>

    <div class="dash-grid">
        <div>
            <h3 class="dash-title">Последние заявки</h3>

            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Номер</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="recentOrders.length === 0">
                            <td colspan="4">
                                <div class="empty">Заявок пока нет</div>
                            </td>
                        </tr>
                        <tr v-for="order in recentOrders" :key="order.id">
                            <td>
                                <Link
                                    class="row-name"
                                    :href="
                                        adminRoutes.orders.show(order.number)
                                    "
                                >
                                    {{ order.number }}
                                </Link>
                            </td>
                            <td>
                                <div class="row-name">
                                    {{ order.customer_name }}
                                </div>
                                <div class="row-sub">{{ order.phone }}</div>
                            </td>
                            <td class="num">{{ money(order.total) }}</td>
                            <td>
                                <span
                                    class="pill"
                                    :class="
                                        order.status === 'new'
                                            ? 'pill-wait'
                                            : 'pill-ok'
                                    "
                                >
                                    {{ order.status_label }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h3 class="dash-title">Заканчивается на складе</h3>

            <div class="panel">
                <div v-if="lowStock.length === 0" class="empty">
                    Остатков хватает
                </div>
                <ul v-else class="mini-list">
                    <li v-for="item in lowStock" :key="item.id">
                        <span>{{ item.brand }} {{ item.model }}</span>
                        <b>{{ item.qty }} шт.</b>
                    </li>
                </ul>
                <Link class="btn" :href="adminRoutes.products.index()">
                    Открыть каталог
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.stat-money {
    font-size: var(--step-2);
}

.dash-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

.dash-title {
    font-size: var(--step-1);
    text-transform: uppercase;
    margin-bottom: 12px;
}

@media (max-width: 900px) {
    .dash-grid {
        grid-template-columns: 1fr;
    }
}
</style>
