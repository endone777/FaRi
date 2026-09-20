<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import contacts from '@/routes/contacts';

const { topics } = defineProps<{ topics: string[] }>();
</script>

<template>
    <Head title="Контакты" />

    <section class="section wrap">
        <div class="section-head">
            <div class="section-title">
                <span class="eyebrow">Контакты</span>
                <h2>Связаться с нами</h2>
            </div>
            <p>
                Вопрос по совместимости, гарантии или доставке — ответим в
                течение рабочего дня.
            </p>
        </div>

        <div class="forms">
            <Form
                v-slot="{ errors, processing, wasSuccessful }"
                class="panel"
                v-bind="contacts.store.form()"
                reset-on-success
            >
                <h3>Обратная связь</h3>

                <div class="field">
                    <label for="name">Имя</label>
                    <input
                        id="name"
                        name="name"
                        required
                        autocomplete="name"
                        placeholder="Ваше имя"
                    />
                    <span v-if="errors.name" class="hint hint-bad">
                        {{ errors.name }}
                    </span>
                </div>

                <div class="field">
                    <label for="contact">Почта или телефон</label>
                    <input
                        id="contact"
                        name="contact"
                        required
                        placeholder="Куда прислать ответ"
                    />
                    <span v-if="errors.contact" class="hint hint-bad">
                        {{ errors.contact }}
                    </span>
                </div>

                <div class="field">
                    <label for="topic">Тема</label>
                    <select id="topic" name="topic">
                        <option v-for="topic in topics" :key="topic">
                            {{ topic }}
                        </option>
                    </select>
                </div>

                <div class="field">
                    <label for="message">Сообщение</label>
                    <textarea
                        id="message"
                        name="message"
                        required
                        placeholder="Опишите, что нужно"
                    />
                    <span v-if="errors.message" class="hint hint-bad">
                        {{ errors.message }}
                    </span>
                </div>

                <button
                    class="btn btn-primary btn-wide"
                    type="submit"
                    :disabled="processing"
                >
                    {{ processing ? 'Отправляем…' : 'Отправить сообщение' }}
                </button>

                <div v-if="wasSuccessful" class="ok-msg on">
                    Сообщение отправлено — ответим в рабочее время.
                </div>
            </Form>

            <div class="panel">
                <h3>Склад и связь</h3>
                <ul class="mini-list">
                    <li>
                        <span>Телефон</span><b class="num">+7 900 000-00-00</b>
                    </li>
                    <li><span>Почта</span><b>zayavki@fari.example</b></li>
                    <li>
                        <span>Адрес</span><b>Москва, ул. Автомоторная, 4с1</b>
                    </li>
                    <li><span>Часы работы</span><b>Пн–Сб, 09:00–20:00</b></li>
                </ul>
                <p class="note">
                    Самовывоз — в день заказа: при вас проверим фару на стенде и
                    посветим на экран. Заказ держим три дня.
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

.hint-bad {
    color: var(--bad);
}
</style>
