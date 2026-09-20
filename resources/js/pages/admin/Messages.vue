<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import Pagination from '@/components/fari/Pagination.vue';
import adminRoutes from '@/routes/admin';
import type { Paginated } from '@/types/fari';

type Message = {
    id: number;
    name: string;
    contact: string;
    topic: string;
    message: string;
    status: string;
    created_at: string;
};

const { messages, statuses } = defineProps<{
    messages: Paginated<Message>;
    statuses: Record<string, string>;
}>();

function setStatus(message: Message, status: string): void {
    router.patch(
        adminRoutes.messages.update(message.id).url,
        { status },
        { preserveScroll: true },
    );
}

function destroy(message: Message): void {
    if (!confirm(`Удалить сообщение от ${message.name}?`)) {
        return;
    }

    router.delete(adminRoutes.messages.destroy(message.id).url, {
        preserveScroll: true,
    });
}

function formatDate(value: string): string {
    return new Date(value).toLocaleString('ru-RU');
}
</script>

<template>
    <Head title="Админка — сообщения" />

    <div class="section-head">
        <div class="section-title">
            <span class="eyebrow">Обратная связь</span>
            <h2>Сообщения с сайта</h2>
        </div>
        <p>Форма на странице контактов и кнопка «Уведомить» пишут сюда.</p>
    </div>

    <div class="flow">
        <div v-if="messages.data.length === 0" class="empty">Сообщений нет</div>

        <article
            v-for="item in messages.data"
            :key="item.id"
            class="panel message"
        >
            <div class="message-head">
                <div>
                    <div class="row-name">
                        {{ item.name }} · {{ item.contact }}
                    </div>
                    <div class="row-sub">
                        {{ item.topic }} · {{ formatDate(item.created_at) }}
                    </div>
                </div>

                <div class="row-acts">
                    <select
                        :value="item.status"
                        @change="
                            setStatus(
                                item,
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
                    >
                        <option
                            v-for="(label, value) in statuses"
                            :key="value"
                            :value="value"
                        >
                            {{ label }}
                        </option>
                    </select>
                    <button
                        class="btn btn-sm btn-danger"
                        type="button"
                        @click="destroy(item)"
                    >
                        Удалить
                    </button>
                </div>
            </div>

            <p>{{ item.message }}</p>
        </article>
    </div>

    <Pagination :links="messages.links" />
</template>

<style scoped>
.section-title {
    display: grid;
    gap: 8px;
}

.message-head {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    align-items: flex-start;
}
</style>
