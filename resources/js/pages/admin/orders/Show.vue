<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { money } from '@/lib/fari';
import adminRoutes from '@/routes/admin';
import type { Order } from '@/types/fari';

const { order, statuses } = defineProps<{
    order: Order;
    statuses: Record<string, string>;
}>();

const form = useForm({
    status: order.status,
    admin_note: order.admin_note ?? '',
});

function save(): void {
    form.patch(adminRoutes.orders.update(order.number).url, {
        preserveScroll: true,
    });
}

function destroy(): void {
    if (!confirm(`Удалить заявку ${order.number}? Действие необратимо.`)) {
        return;
    }

    router.delete(adminRoutes.orders.destroy(order.number).url);
}

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleString('ru-RU') : '—';
}
</script>

<template>
    <Head :title="`Заявка ${order.number}`" />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow"
                >Заявка · {{ formatDate(order.created_at) }}</span
            >
            <h2>{{ order.number }}</h2>
        </div>
        <Link class="btn" :href="adminRoutes.orders.index()">
            ← Ко всем заявкам
        </Link>
    </div>

    <div class="order-grid">
        <div class="panel">
            <h3>Состав</h3>

            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Позиция</th>
                            <th>Цена</th>
                            <th>Комплектов</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in order.items" :key="item.id">
                            <td>
                                <div class="row-name">{{ item.title }}</div>
                                <div class="row-sub">{{ item.oem }}</div>
                            </td>
                            <td class="num">{{ money(item.unit_price) }}</td>
                            <td class="num">{{ item.qty }}</td>
                            <td class="num">{{ money(item.line_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <ul class="mini-list">
                <li>
                    <span>Товары</span><b>{{ money(order.items_total) }}</b>
                </li>
                <li>
                    <span>{{ order.delivery_name }}</span>
                    <b>
                        {{
                            order.delivery_cost === 0
                                ? 'бесплатно'
                                : money(order.delivery_cost)
                        }}
                    </b>
                </li>
            </ul>

            <div class="total">
                <span>Итого</span>
                <b>{{ money(order.total) }}</b>
            </div>
        </div>

        <div class="order-side">
            <div class="panel">
                <h3>Клиент</h3>
                <ul class="mini-list">
                    <li>
                        <span>Имя</span><b>{{ order.customer_name }}</b>
                    </li>
                    <li>
                        <span>Телефон</span><b>{{ order.phone }}</b>
                    </li>
                    <li>
                        <span>Почта</span><b>{{ order.email }}</b>
                    </li>
                    <li>
                        <span>VIN</span><b>{{ order.vin || '—' }}</b>
                    </li>
                    <li>
                        <span>Город</span><b>{{ order.city || '—' }}</b>
                    </li>
                </ul>
                <p v-if="order.comment" class="note">{{ order.comment }}</p>
            </div>

            <form class="panel" @submit.prevent="save">
                <h3>Обработка</h3>

                <div class="field">
                    <label for="status">Статус</label>
                    <select id="status" v-model="form.status">
                        <option
                            v-for="(label, value) in statuses"
                            :key="value"
                            :value="value"
                        >
                            {{ label }}
                        </option>
                    </select>
                </div>

                <div class="field">
                    <label for="admin_note">Заметка для своих</label>
                    <textarea
                        id="admin_note"
                        v-model="form.admin_note"
                        placeholder="Что проверено, когда выставлен счёт"
                    />
                </div>

                <button
                    class="btn btn-primary btn-wide"
                    type="submit"
                    :disabled="form.processing"
                >
                    Сохранить
                </button>

                <button
                    class="btn btn-danger btn-wide"
                    type="button"
                    @click="destroy"
                >
                    Удалить заявку
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.order-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

.order-side {
    display: grid;
    gap: 20px;
}

@media (max-width: 900px) {
    .order-grid {
        grid-template-columns: 1fr;
    }
}
</style>
