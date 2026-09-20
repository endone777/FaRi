<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ThemeToggle from '@/components/fari/ThemeToggle.vue';
import { logout } from '@/routes';
import adminRoutes from '@/routes/admin';
import { home } from '@/routes';

const page = usePage();

const current = computed(() => page.url.split('?')[0]);

const tabs = computed(() => [
    { label: 'Сводка', href: adminRoutes.dashboard(), match: '/admin' },
    {
        label: 'Каталог',
        href: adminRoutes.products.index(),
        match: '/admin/products',
    },
    {
        label: 'Автомобили',
        href: adminRoutes.cars.index(),
        match: '/admin/cars',
    },
    {
        label: 'Заявки',
        href: adminRoutes.orders.index(),
        match: '/admin/orders',
    },
    {
        label: 'Звонки',
        href: adminRoutes.callbacks.index(),
        match: '/admin/callbacks',
    },
    {
        label: 'Сообщения',
        href: adminRoutes.messages.index(),
        match: '/admin/messages',
    },
    {
        label: 'Доставка',
        href: adminRoutes.delivery.index(),
        match: '/admin/delivery',
    },
]);

function isOn(match: string): boolean {
    return match === '/admin'
        ? current.value === '/admin'
        : current.value.startsWith(match);
}

function signOut(): void {
    router.post(logout.url());
}
</script>

<template>
    <div class="fari">
        <header class="top">
            <div class="wrap top-in">
                <Link class="logo" :href="home()">
                    <span class="logo-beam" />FARI <small>админка</small>
                </Link>

                <div class="top-actions top-actions-admin admin-actions">
                    <Link class="icon-btn" :href="home()">← На сайт</Link>
                    <ThemeToggle />
                    <button class="icon-btn" type="button" @click="signOut">
                        Выйти
                    </button>
                </div>
            </div>
        </header>

        <main class="section wrap admin-main">
            <nav class="admin-nav">
                <Link
                    v-for="tab in tabs"
                    :key="tab.label"
                    :href="tab.href"
                    :class="{ on: isOn(tab.match) }"
                >
                    {{ tab.label }}
                </Link>
            </nav>

            <slot />
        </main>
    </div>
</template>

<style scoped>
.admin-actions {
    margin-inline-start: auto;
}

.admin-main {
    flex: 1;
    padding-block: 32px 64px;
}
</style>
