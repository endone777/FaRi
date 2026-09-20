<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { money } from '@/lib/fari';
import adminRoutes from '@/routes/admin';
import type { DeliveryMethod } from '@/types/fari';

const { methods } = defineProps<{ methods: DeliveryMethod[] }>();

const editing = ref<DeliveryMethod | null>(null);

const form = useForm({
    code: '',
    name: '',
    cost: 0,
    days: '',
    note: '',
    free_from: null as number | null,
    is_active: true,
    position: 0,
});

function edit(method: DeliveryMethod): void {
    editing.value = method;
    form.defaults({
        code: method.code,
        name: method.name,
        cost: method.cost,
        days: method.days,
        note: method.note ?? '',
        free_from: method.free_from,
        is_active: method.is_active,
        position: method.position,
    });
    form.reset();
    form.clearErrors();
}

function reset(): void {
    editing.value = null;
    form.defaults({
        code: '',
        name: '',
        cost: 0,
        days: '',
        note: '',
        free_from: null,
        is_active: true,
        position: methods.length + 1,
    });
    form.reset();
    form.clearErrors();
}

function submit(): void {
    if (editing.value) {
        form.patch(adminRoutes.delivery.update(editing.value.id).url, {
            preserveScroll: true,
            onSuccess: reset,
        });

        return;
    }

    form.post(adminRoutes.delivery.store().url, {
        preserveScroll: true,
        onSuccess: reset,
    });
}

function destroy(method: DeliveryMethod): void {
    if (!confirm(`Удалить способ «${method.name}»?`)) {
        return;
    }

    router.delete(adminRoutes.delivery.destroy(method.id).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Админка — доставка" />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow">Доставка</span>
            <h2>Способы и тарифы</h2>
        </div>
        <p>
            Эти способы клиент видит в карточке товара, в калькуляторе и при
            оформлении заявки.
        </p>
    </div>

    <div class="admin-grid">
        <form class="panel admin-form" novalidate @submit.prevent="submit">
            <h3>{{ editing ? 'Правка способа' : 'Новый способ' }}</h3>

            <div class="row2">
                <div class="field">
                    <label for="code">Код</label>
                    <input id="code" v-model="form.code" placeholder="pickup" />
                    <span v-if="form.errors.code" class="hint hint-bad">
                        {{ form.errors.code }}
                    </span>
                </div>
                <div class="field">
                    <label for="position">Порядок</label>
                    <input
                        id="position"
                        v-model.number="form.position"
                        class="num"
                        type="number"
                        min="0"
                    />
                </div>
            </div>

            <div class="field">
                <label for="name">Название</label>
                <input
                    id="name"
                    v-model="form.name"
                    placeholder="Курьер по Москве"
                />
                <span v-if="form.errors.name" class="hint hint-bad">
                    {{ form.errors.name }}
                </span>
            </div>

            <div class="row2">
                <div class="field">
                    <label for="cost">Стоимость, ₽</label>
                    <input
                        id="cost"
                        v-model.number="form.cost"
                        class="num"
                        type="number"
                        min="0"
                    />
                </div>
                <div class="field">
                    <label for="days">Срок</label>
                    <input
                        id="days"
                        v-model="form.days"
                        placeholder="1–2 дня"
                    />
                    <span v-if="form.errors.days" class="hint hint-bad">
                        {{ form.errors.days }}
                    </span>
                </div>
            </div>

            <div class="field">
                <label for="free_from">Бесплатно от суммы, ₽</label>
                <input
                    id="free_from"
                    v-model.number="form.free_from"
                    class="num"
                    type="number"
                    min="0"
                    placeholder="оставьте пустым, если нет"
                />
            </div>

            <div class="field">
                <label for="note">Описание</label>
                <textarea
                    id="note"
                    v-model="form.note"
                    placeholder="Что важно знать клиенту"
                />
            </div>

            <label class="switch">
                <input v-model="form.is_active" type="checkbox" />
                <span>Показывать на витрине</span>
            </label>

            <div class="form-actions">
                <button
                    class="btn btn-primary"
                    type="submit"
                    :disabled="form.processing"
                >
                    {{ editing ? 'Сохранить' : 'Добавить' }}
                </button>
                <button class="btn" type="button" @click="reset">
                    Очистить
                </button>
            </div>
        </form>

        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Способ</th>
                        <th>Стоимость</th>
                        <th>Срок</th>
                        <th>Витрина</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="method in methods"
                        :key="method.id"
                        :class="{ editing: editing?.id === method.id }"
                    >
                        <td>
                            <div class="row-name">{{ method.name }}</div>
                            <div class="row-sub">{{ method.code }}</div>
                        </td>
                        <td class="num">
                            {{
                                method.cost === 0
                                    ? 'бесплатно'
                                    : money(method.cost)
                            }}
                            <div v-if="method.free_from" class="row-sub">
                                бесплатно от {{ money(method.free_from) }}
                            </div>
                        </td>
                        <td class="row-sub">{{ method.days }}</td>
                        <td>
                            <span
                                class="pill"
                                :class="
                                    method.is_active ? 'pill-ok' : 'pill-wait'
                                "
                            >
                                {{ method.is_active ? 'виден' : 'скрыт' }}
                            </span>
                        </td>
                        <td>
                            <div class="row-acts">
                                <button
                                    class="btn btn-sm"
                                    type="button"
                                    @click="edit(method)"
                                >
                                    Изменить
                                </button>
                                <button
                                    class="btn btn-sm btn-danger"
                                    type="button"
                                    @click="destroy(method)"
                                >
                                    Удалить
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.hint-bad {
    color: var(--bad);
}

.switch {
    display: flex;
    gap: 8px;
    align-items: center;
    font-size: var(--step--1);
}

.form-actions {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
}
</style>
