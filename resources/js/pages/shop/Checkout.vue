<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCart } from '@/composables/useCart';
import { money } from '@/lib/fari';
import cart from '@/routes/cart';
import checkout from '@/routes/checkout';
import type { DeliveryMethod } from '@/types/fari';

const { deliveryMethods } = defineProps<{
    deliveryMethods: DeliveryMethod[];
}>();

const { cart: basket } = useCart();

const methodId = ref<number>(deliveryMethods[0]?.id ?? 0);

const method = computed(() =>
    deliveryMethods.find((item) => item.id === methodId.value),
);

const deliveryCost = computed(() => {
    const selected = method.value;

    if (!selected) {
        return 0;
    }

    if (
        selected.free_from !== null &&
        basket.value.items_total >= selected.free_from
    ) {
        return 0;
    }

    return selected.cost;
});

const total = computed(() => basket.value.items_total + deliveryCost.value);
</script>

<template>
    <Head title="Оформление заявки" />

    <section class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Заявка</span>
                <h2>Оформление без регистрации</h2>
            </div>
            <p>
                Оставьте контакты — менеджер проверит наличие под ваш VIN,
                пришлёт счёт и сроки. Аккаунт создавать не нужно.
            </p>
        </div>

        <div class="checkout">
            <Form
                v-slot="{ errors, processing }"
                class="panel"
                v-bind="checkout.store.form()"
            >
                <h3>Контакты</h3>

                <div class="row2">
                    <div class="field">
                        <label for="customer_name">Имя</label>
                        <input
                            id="customer_name"
                            name="customer_name"
                            required
                            autocomplete="name"
                            placeholder="Как к вам обращаться"
                        />
                        <span v-if="errors.customer_name" class="hint hint-bad">
                            {{ errors.customer_name }}
                        </span>
                    </div>
                    <div class="field">
                        <label for="phone">Телефон</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            required
                            autocomplete="tel"
                            placeholder="+7 900 000-00-00"
                        />
                        <span v-if="errors.phone" class="hint hint-bad">
                            {{ errors.phone }}
                        </span>
                    </div>
                </div>

                <div class="row2">
                    <div class="field">
                        <label for="email">Почта</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="you@mail.ru"
                        />
                        <span v-if="errors.email" class="hint hint-bad">
                            {{ errors.email }}
                        </span>
                    </div>
                    <div class="field">
                        <label for="vin">VIN (необязательно)</label>
                        <input
                            id="vin"
                            name="vin"
                            maxlength="17"
                            placeholder="17 символов"
                        />
                        <span v-if="errors.vin" class="hint hint-bad">
                            {{ errors.vin }}
                        </span>
                    </div>
                </div>

                <h3>Доставка</h3>

                <div class="field">
                    <label for="delivery_method_id">Способ</label>
                    <select
                        id="delivery_method_id"
                        v-model="methodId"
                        name="delivery_method_id"
                        required
                    >
                        <option
                            v-for="item in deliveryMethods"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.name }} ·
                            {{
                                item.cost === 0 ? 'бесплатно' : money(item.cost)
                            }}
                            · {{ item.days }}
                        </option>
                    </select>
                    <span v-if="method?.note" class="hint">
                        {{ method.note }}
                    </span>
                    <span
                        v-if="errors.delivery_method_id"
                        class="hint hint-bad"
                    >
                        {{ errors.delivery_method_id }}
                    </span>
                </div>

                <div class="field">
                    <label for="city">Город</label>
                    <input
                        id="city"
                        name="city"
                        value="Москва"
                        placeholder="Куда везём"
                    />
                </div>

                <div class="field">
                    <label for="comment">Комментарий</label>
                    <textarea
                        id="comment"
                        name="comment"
                        placeholder="Срочность, вопросы по установке, удобное время"
                    />
                </div>

                <button
                    class="btn btn-primary btn-wide"
                    type="submit"
                    :disabled="processing || basket.lines.length === 0"
                >
                    {{ processing ? 'Отправляем…' : 'Отправить заявку' }}
                </button>

                <p class="note">
                    Отправляя заявку, вы соглашаетесь на обработку контактных
                    данных для связи по этому заказу.
                </p>
            </Form>

            <aside class="panel checkout-summary">
                <h3>Состав заявки</h3>

                <ul class="mini-list">
                    <li v-for="line in basket.lines" :key="line.key">
                        <span>
                            {{ line.product.brand }} {{ line.product.model }} —
                            комплект × {{ line.qty }}
                        </span>
                        <b>{{ money(line.line_total) }}</b>
                    </li>
                </ul>

                <ul class="mini-list">
                    <li>
                        <span>Товары</span>
                        <b>{{ money(basket.items_total) }}</b>
                    </li>
                    <li>
                        <span>Доставка</span>
                        <b>
                            {{
                                deliveryCost === 0
                                    ? 'бесплатно'
                                    : money(deliveryCost)
                            }}
                        </b>
                    </li>
                </ul>

                <div class="total">
                    <span>Итого</span>
                    <b>{{ money(total) }}</b>
                </div>

                <Link class="btn" :href="cart.index()"
                    >Вернуться в корзину</Link
                >
            </aside>
        </div>
    </section>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.checkout {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

.checkout-summary {
    position: sticky;
    top: 82px;
}

.hint-bad {
    color: var(--bad);
}

@media (max-width: 900px) {
    .checkout {
        grid-template-columns: 1fr;
    }

    .checkout-summary {
        position: static;
    }
}
</style>
