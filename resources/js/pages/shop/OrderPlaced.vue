<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { money } from '@/lib/fari';
import catalog from '@/routes/catalog';
import type { Order } from '@/types/fari';

const { order } = defineProps<{ order: Order }>();
</script>

<template>
    <Head :title="`Заявка ${order.number}`" />

    <section class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Заявка принята</span>
                <h2>Номер {{ order.number }}</h2>
            </div>
            <p>
                Сохраните эту страницу — по ссылке всегда видно состав заявки.
                Менеджер свяжется по телефону {{ order.phone }} в рабочее время.
            </p>
        </div>

        <div class="panel order-panel">
            <ul class="mini-list">
                <li v-for="item in order.items" :key="item.id">
                    <span> {{ item.title }} · комплект × {{ item.qty }} </span>
                    <b>{{ money(item.line_total) }}</b>
                </li>
            </ul>

            <ul class="mini-list">
                <li>
                    <span>Товары</span>
                    <b>{{ money(order.items_total) }}</b>
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
                <li>
                    <span>Статус</span>
                    <b>{{ order.status_label }}</b>
                </li>
            </ul>

            <div class="total">
                <span>Итого</span>
                <b>{{ money(order.total) }}</b>
            </div>

            <Link class="btn btn-primary" :href="catalog.index()">
                Вернуться в каталог
            </Link>
        </div>
    </section>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.order-panel {
    max-width: 640px;
}
</style>
