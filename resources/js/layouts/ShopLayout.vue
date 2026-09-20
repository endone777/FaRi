<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CartDrawer from '@/components/fari/CartDrawer.vue';
import ThemeToggle from '@/components/fari/ThemeToggle.vue';
import { useCart } from '@/composables/useCart';
import { dashboard as adminDashboard } from '@/routes/admin';
import catalog from '@/routes/catalog';
import contacts from '@/routes/contacts';
import { delivery, home } from '@/routes';
import CallbackModal from '@/components/fari/CallbackModal.vue';

const page = usePage();
const { cart } = useCart();

const drawerOpen = ref(false);
const callbackOpen = ref(false);

const isAdmin = computed(() => page.props.isAdmin === true);
</script>

<template>
    <div class="fari">
        <header class="top">
            <div class="wrap top-in">
                <Link class="logo" :href="home()">
                    <span class="logo-beam" />FARI
                </Link>

                <nav class="nav">
                    <Link :href="catalog.index()">Каталог</Link>
                    <Link :href="delivery()">Доставка</Link>
                    <Link :href="contacts.index()">Контакты</Link>
                    <Link v-if="isAdmin" :href="adminDashboard()">Админка</Link>
                </nav>

                <div class="top-actions">
                    <button
                        class="icon-btn"
                        type="button"
                        @click="callbackOpen = true"
                    >
                        Заказать звонок
                    </button>
                    <ThemeToggle />
                    <button
                        class="icon-btn"
                        type="button"
                        @click="drawerOpen = true"
                    >
                        Корзина
                        <span class="cart-count">{{ cart.count }}</span>
                    </button>
                </div>
            </div>
        </header>

        <main class="shop-main">
            <slot />
        </main>

        <footer class="foot">
            <div class="wrap">
                <div class="foot-in">
                    <div class="foot-col">
                        <b>FARI</b>
                        <span>Оптика в сборе и её компоненты</span>
                        <span>Пн–Сб, 09:00–20:00</span>
                        <Link :href="catalog.index()">Каталог</Link>
                    </div>
                    <div class="foot-col">
                        <b>Связь</b>
                        <span class="num">+7 900 000-00-00</span>
                        <span>zayavki@fari.example</span>
                        <span>Москва, ул. Автомоторная, 4с1</span>
                    </div>
                    <div class="foot-col">
                        <b>Важно</b>
                        <span>
                            Заказ оформляется без регистрации: вы оставляете
                            заявку, менеджер проверяет наличие и присылает счёт.
                        </span>
                        <span>
                            BMW, Audi, Volkswagen и Toyota — марки их
                            правообладателей, указаны как применимость.
                        </span>
                    </div>
                </div>
            </div>
        </footer>

        <CartDrawer v-model:open="drawerOpen" />
        <CallbackModal v-model:open="callbackOpen" />
    </div>
</template>

<style scoped>
.shop-main {
    flex: 1;
}
</style>
