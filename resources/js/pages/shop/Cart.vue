<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';
import { money } from '@/lib/fari';
import catalog from '@/routes/catalog';
import checkout from '@/routes/checkout';

const { cart, setQty, remove, clear } = useCart();
</script>

<template>
    <Head title="Корзина" />

    <section class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Корзина</span>
                <h2>Что поедет к вам</h2>
            </div>
            <p>
                Деньги на сайте не принимаем. Вы оставляете заявку, менеджер
                проверяет наличие под ваш VIN и присылает счёт. Регистрация не
                нужна.
            </p>
        </div>

        <div v-if="cart.lines.length === 0" class="empty">
            Корзина пуста.<br />
            <Link :href="catalog.index()">Перейти в каталог</Link>
        </div>

        <template v-else>
            <div class="tbl-wrap cart-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Позиция</th>
                            <th>Цена</th>
                            <th>Комплектов</th>
                            <th>Сумма</th>
                            <th />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="line in cart.lines" :key="line.key">
                            <td>
                                <div class="row-name">
                                    <Link
                                        :href="catalog.show(line.product.slug)"
                                    >
                                        {{ line.product.brand }}
                                        {{ line.product.model }}
                                    </Link>
                                </div>
                                <div class="row-sub">
                                    {{ line.product.name }} · комплект ·
                                    {{ line.product.oem }}
                                </div>
                            </td>
                            <td class="num" data-label="Цена">
                                {{ money(line.product.price) }}
                            </td>
                            <td data-label="Комплектов">
                                <div class="qty">
                                    <button
                                        type="button"
                                        aria-label="Меньше"
                                        @click="setQty(line.key, line.qty - 1)"
                                    >
                                        −
                                    </button>
                                    <span>{{ line.qty }}</span>
                                    <button
                                        type="button"
                                        aria-label="Больше"
                                        @click="setQty(line.key, line.qty + 1)"
                                    >
                                        +
                                    </button>
                                </div>
                            </td>
                            <td class="num" data-label="Сумма">
                                {{ money(line.line_total) }}
                            </td>
                            <td>
                                <div class="row-acts">
                                    <button
                                        class="btn btn-sm btn-danger"
                                        type="button"
                                        @click="remove(line.key)"
                                    >
                                        Убрать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="cart-foot">
                <div class="cart-sum">
                    <div class="total">
                        <span>Товары</span>
                        <b>{{ money(cart.items_total) }}</b>
                    </div>
                    <p class="hint">
                        Вес заказа — {{ cart.weight }} кг. Стоимость доставки
                        посчитаем на следующем шаге.
                    </p>
                </div>

                <div class="cart-actions">
                    <button class="btn" type="button" @click="clear">
                        Очистить
                    </button>
                    <Link class="btn" :href="catalog.index()">
                        Продолжить выбор
                    </Link>
                    <Link class="btn btn-primary" :href="checkout.create()">
                        Оформить заявку
                    </Link>
                </div>
            </div>
        </template>
    </section>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

/* На телефоне таблица разворачивается в карточки. */
@media (max-width: 720px) {
    .cart-wrap {
        border: 0;
        background: transparent;
        box-shadow: none;
        overflow: visible;
    }

    .cart-table {
        min-width: 0;
        display: block;
    }

    .cart-table thead {
        display: none;
    }

    .cart-table tbody,
    .cart-table tr,
    .cart-table td {
        display: block;
        width: 100%;
    }

    .cart-table tr {
        margin-bottom: 12px;
        padding: 14px 16px;
        border: 1px solid var(--line);
        border-radius: var(--r-lg);
        background: var(--surface);
        box-shadow: var(--shadow);
    }

    .cart-table td {
        border: 0;
        padding: 5px 0;
    }

    .cart-table td[data-label] {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .cart-table td[data-label]::before {
        content: attr(data-label);
        font-family: var(--f-data);
        font-size: 0.68rem;
        letter-spacing: 0.11em;
        text-transform: uppercase;
        color: var(--text-mute);
    }

    .cart-table .row-acts {
        justify-content: stretch;
    }

    .cart-table .row-acts .btn {
        width: 100%;
    }
}

.cart-foot {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.cart-sum {
    display: grid;
    gap: 6px;
    min-width: 260px;
}

.cart-sum .hint {
    font-size: var(--step--1);
    color: var(--text-mute);
}

.cart-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
</style>
