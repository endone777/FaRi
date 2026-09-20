<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PageLink } from '@/types/fari';

const { links } = defineProps<{ links: PageLink[] }>();
</script>

<template>
    <nav v-if="links.length > 3" class="pager" aria-label="Страницы">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                :class="{ on: link.active }"
                preserve-scroll
                v-html="link.label"
            />
            <span v-else class="off" v-html="link.label" />
        </template>
    </nav>
</template>
