<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { watch } from 'vue';
import callback from '@/routes/callback';

const open = defineModel<boolean>('open', { default: false });

const TIMES = [
    'Как можно скорее',
    'Сегодня до обеда',
    'Сегодня после обеда',
    'Завтра',
];

watch(open, (value) => {
    document.body.style.overflow = value ? 'hidden' : '';
});
</script>

<template>
    <div class="modal" :class="{ on: open }" role="dialog" aria-modal="true">
        <div class="modal-card modal-narrow">
            <button
                class="modal-x"
                type="button"
                aria-label="Закрыть"
                @click="open = false"
            >
                ×
            </button>

            <Form
                v-slot="{ errors, processing, wasSuccessful }"
                class="panel panel-flat"
                v-bind="callback.store.form()"
                reset-on-success
            >
                <h3>Заказать звонок</h3>
                <p>Перезвоним в рабочее время, обычно в течение получаса.</p>

                <div class="field">
                    <label for="callback-name">Имя</label>
                    <input
                        id="callback-name"
                        name="name"
                        required
                        placeholder="Как к вам обращаться"
                    />
                    <span v-if="errors.name" class="hint hint-bad">
                        {{ errors.name }}
                    </span>
                </div>

                <div class="field">
                    <label for="callback-phone">Телефон</label>
                    <input
                        id="callback-phone"
                        name="phone"
                        type="tel"
                        required
                        placeholder="+7 900 000-00-00"
                    />
                    <span v-if="errors.phone" class="hint hint-bad">
                        {{ errors.phone }}
                    </span>
                </div>

                <div class="field">
                    <label for="callback-when">Когда удобно</label>
                    <select id="callback-when" name="preferred_time">
                        <option v-for="time in TIMES" :key="time">
                            {{ time }}
                        </option>
                    </select>
                </div>

                <button
                    class="btn btn-primary btn-wide"
                    type="submit"
                    :disabled="processing"
                >
                    {{ processing ? 'Отправляем…' : 'Жду звонка' }}
                </button>

                <div v-if="wasSuccessful" class="ok-msg on">
                    Заявка принята — перезвоним.
                </div>
            </Form>
        </div>
    </div>
</template>

<style scoped>
.modal-narrow {
    max-width: 420px;
}

@media (max-width: 720px) {
    .modal-narrow {
        max-width: none;
        min-height: 100%;
        border: 0;
        border-radius: 0;
    }
}

.panel-flat {
    border: 0;
    box-shadow: none;
}

.hint-bad {
    color: var(--bad);
}
</style>
