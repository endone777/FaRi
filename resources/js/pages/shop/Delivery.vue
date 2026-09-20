<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCart } from '@/composables/useCart';
import { money } from '@/lib/fari';
import type { DeliveryMethod } from '@/types/fari';

const { deliveryMethods } = defineProps<{
    deliveryMethods: DeliveryMethod[];
}>();

const { cart } = useCart();

const methodId = ref<number>(deliveryMethods[0]?.id ?? 0);
const city = ref('Москва');
const weight = ref(cart.value.weight);

const method = computed(() =>
    deliveryMethods.find((item) => item.id === methodId.value),
);

const cost = computed(() => {
    const selected = method.value;

    if (!selected) {
        return 0;
    }

    if (
        selected.free_from !== null &&
        cart.value.items_total >= selected.free_from
    ) {
        return 0;
    }

    return selected.cost;
});

const verdict = computed(() => {
    const selected = method.value;

    if (!selected) {
        return '';
    }

    if (selected.code === 'post' && weight.value > 3) {
        return 'Почтой отправляем только позиции до 3 кг. Для фары в сборе выберите транспортную компанию.';
    }

    return `${selected.name} в город ${city.value || '—'}: ${
        cost.value === 0 ? 'бесплатно' : money(cost.value)
    }, срок ${selected.days}.`;
});
</script>

<template>
    <Head title="Доставка и возврат" />

    <section class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Доставка и самовывоз</span>
                <h2>Как фара доедет</h2>
            </div>
            <p>
                Фара — хрупкий и объёмный груз. Пакуем в жёсткий короб с
                пенопластовым ложементом и снимаем видео упаковки: если
                перевозчик разобьёт, спор закрывается в вашу пользу.
            </p>
        </div>

        <div class="ship-grid">
            <article
                v-for="item in deliveryMethods"
                :key="item.id"
                class="ship"
            >
                <h4>{{ item.name }}</h4>
                <div class="ship-meta">
                    <span class="num">
                        {{ item.cost === 0 ? 'бесплатно' : money(item.cost) }}
                    </span>
                    <span>{{ item.days }}</span>
                </div>
                <p>{{ item.note }}</p>
            </article>
        </div>

        <div class="calc">
            <div class="field">
                <label for="calc-way">Способ</label>
                <select id="calc-way" v-model="methodId">
                    <option
                        v-for="item in deliveryMethods"
                        :key="item.id"
                        :value="item.id"
                    >
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <div class="field">
                <label for="calc-city">Город</label>
                <input id="calc-city" v-model="city" placeholder="Куда везём" />
            </div>
            <div class="field">
                <label for="calc-weight">Вес заказа, кг</label>
                <input
                    id="calc-weight"
                    v-model.number="weight"
                    class="num"
                    type="number"
                    min="0"
                    step="0.1"
                />
            </div>
            <div class="calc-out" aria-live="polite">{{ verdict }}</div>
        </div>

        <div class="forms delivery-forms">
            <div class="panel">
                <h3>Что мы проверяем перед отправкой</h3>
                <ul class="mini-list">
                    <li><span>Стекло и корпус</span><b>без трещин</b></li>
                    <li><span>Крепёжные уши</span><b>целые, не клееные</b></li>
                    <li><span>Свет на стенде</span><b>граница светотени</b></li>
                    <li><span>Разъёмы и шлейфы</span><b>без окисления</b></li>
                    <li>
                        <span>Фото перед упаковкой</span><b>отправляем вам</b>
                    </li>
                </ul>
                <p class="note">
                    Если при получении вы видите бой — не забирайте посылку и
                    позвоните нам. Отправим замену со склада или вернём деньги.
                </p>
            </div>

            <div class="panel">
                <h3>Возврат и гарантия</h3>
                <ul class="mini-list">
                    <li><span>Гарантия на оптику</span><b>12 месяцев</b></li>
                    <li><span>Возврат без причины</span><b>14 дней</b></li>
                    <li>
                        <span>Не подошла по VIN</span><b>меняем за свой счёт</b>
                    </li>
                    <li>
                        <span>Брак из коробки</span><b>замена без экспертизы</b>
                    </li>
                </ul>
                <p class="note">
                    Возврат принимаем в родной упаковке и без следов установки.
                    Деньги возвращаем в течение трёх рабочих дней после
                    получения посылки.
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.delivery-forms {
    margin-top: 20px;
}
</style>
