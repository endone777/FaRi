<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();

function toggle(): void {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <button
        class="icon-btn"
        type="button"
        title="Сменить тему"
        aria-label="Сменить тему"
        @click="toggle"
    >
        ◐
    </button>
</template>
