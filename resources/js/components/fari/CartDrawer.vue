<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCart } from '@/composables/useCart';
import { money, SIDES } from '@/lib/fari';
import checkout from '@/routes/checkout';

const open = defineModel<boolean>('open', { default: false });

const { cart, setQty, remove } = useCart();
</script>

<template>
    <div>
        <div class="scrim" :class="{ on: open }" @click="open = false" />

        <aside class="drawer" :class="{ on: open }" aria-label="Корзина">
            <div class="drawer-head">
                <h3>Корзина</h3>
                <button
                    class="x"
                    type="button"
                    aria-label="Закрыть"
                    @click="open = false"
                >
                    ×
                </button>
            </div>

            <div class="drawer-body">
                <div v-if="cart.lines.length === 0" class="empty">
                    Пока пусто.<br />Выберите фару в каталоге.
                </div>

                <div v-for="line in cart.lines" :key="line.key" class="line">
                    <img
                        v-if="line.product.photo"
                        class="line-vis"
                        :src="line.product.photo"
                        :alt="line.product.title"
                    />
                    <span v-else class="line-vis line-vis-empty">схема</span>

                    <div>
                        <div class="line-name">
                            {{ line.product.brand }} {{ line.product.model }}
                        </div>
                        <div class="line-sub">
                            {{ line.product.name }} ·
                            {{ SIDES[line.side] ?? line.side }}
                        </div>
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
                    </div>

                    <div class="line-end">
                        <b class="num">{{ money(line.line_total) }}</b>
                        <button
                            class="btn btn-sm btn-danger"
                            type="button"
                            @click="remove(line.key)"
                        >
                            Убрать
                        </button>
                    </div>
                </div>
            </div>

            <div class="drawer-foot">
                <div class="total">
                    <span>Итого</span>
                    <b>{{ money(cart.items_total) }}</b>
                </div>
                <Link
                    v-if="cart.lines.length > 0"
                    class="btn btn-primary btn-wide"
                    :href="checkout.create()"
                    @click="open = false"
                >
                    Перейти к заявке
                </Link>
                <button v-else class="btn btn-wide" type="button" disabled>
                    Перейти к заявке
                </button>
            </div>
        </aside>
    </div>
</template>

<style scoped>
.line-vis {
    width: 52px;
    height: 38px;
    object-fit: cover;
    border-radius: var(--r-sm);
    border: 1px solid var(--line);
    display: block;
}

.line-vis-empty {
    display: grid;
    place-items: center;
    font-size: 0.6rem;
    color: var(--text-mute);
    background: var(--stage-2);
}

.line-end {
    display: grid;
    gap: 6px;
    justify-items: end;
}
</style>
