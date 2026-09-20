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
            <div class="tbl-wrap">
                <table>
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
                            <td class="num">{{ money(line.product.price) }}</td>
                            <td>
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
                            <td class="num">{{ money(line.line_total) }}</td>
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
