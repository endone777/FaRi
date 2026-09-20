<script setup lang="ts">
import { computed } from 'vue';
import { beamColor, GRILLE_DEFAULT, GRILLES, SHAPES } from '@/lib/fari';
import type { Product } from '@/types/fari';

const {
    brand = 'BMW',
    fitted = null,
    side = 'left',
} = defineProps<{
    brand?: string;
    fitted?: Product | null;
    side?: string;
}>();

const glow = computed(() => (fitted ? beamColor(fitted.color_temp) : '#ffffff'));
const path = computed(() => SHAPES[fitted?.shape ?? 'sharp'] ?? SHAPES.sharp);
const grille = computed(() => GRILLES[brand] ?? GRILLE_DEFAULT);

function isLit(which: string): boolean {
    return fitted !== null && side === which;
}

function beamPath(which: string): string {
    const x = which === 'left' ? 176 : 544;

    return `M${x - 78} 196 L${x + 78} 196 L${x + 236} 320 L${x - 236} 320 Z`;
}

function beamCx(which: string): number {
    return which === 'left' ? 176 : 544;
}
</script>

<template>
    <svg
        viewBox="0 0 720 320"
        role="img"
        :aria-label="`Схема передней части автомобиля ${brand}`"
    >
        <defs>
            <linearGradient id="beamGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" :stop-color="glow" stop-opacity=".75" />
                <stop offset="100%" :stop-color="glow" stop-opacity="0" />
            </linearGradient>
            <radialGradient id="lampOn" cx="38%" cy="42%">
                <stop offset="0%" stop-color="#FFFFFF" />
                <stop offset="45%" :stop-color="glow" />
                <stop offset="100%" :stop-color="glow" stop-opacity=".45" />
            </radialGradient>
            <filter
                id="soft"
                x="-60%"
                y="-60%"
                width="220%"
                height="220%"
            >
                <feGaussianBlur stdDeviation="16" />
            </filter>
        </defs>

        <path
            d="M44 118 Q60 74 130 66 L590 66 Q660 74 676 118 L690 236 Q690 264 660 264 L60 264 Q30 264 30 236 Z"
            fill="var(--car-body)"
            stroke="var(--car-ink)"
            stroke-width="2"
        />
        <path
            d="M120 66 Q360 42 600 66"
            fill="none"
            stroke="var(--car-ink)"
            stroke-width="2"
            opacity=".7"
        />
        <path
            d="M40 246 h640"
            fill="none"
            stroke="var(--car-ink)"
            stroke-width="2"
            opacity=".6"
        />
        <g fill="var(--car-ink)" opacity=".9" v-html="grille" />

        <template v-for="which in ['left', 'right']" :key="`beam-${which}`">
            <g v-if="isLit(which)" filter="url(#soft)" opacity=".85">
                <path :d="beamPath(which)" fill="url(#beamGrad)" />
            </g>
            <ellipse
                v-if="isLit(which)"
                :cx="beamCx(which)"
                cy="176"
                rx="96"
                ry="46"
                :fill="glow"
                opacity=".28"
                filter="url(#soft)"
            />
        </template>

        <g
            v-for="which in ['left', 'right']"
            :key="`lamp-${which}`"
            :fill="isLit(which) ? 'url(#lampOn)' : 'var(--car-ink)'"
            :stroke="isLit(which) ? glow : 'var(--car-ink)'"
            stroke-width="2.5"
            :stroke-dasharray="isLit(which) ? undefined : '7 6'"
            :opacity="isLit(which) ? 1 : 0.55"
        >
            <path v-if="which === 'left'" :d="path" />
            <g v-else transform="translate(720,0) scale(-1,1)">
                <path :d="path" />
            </g>
        </g>

        <template v-if="!fitted">
            <text
                v-for="x in [176, 544]"
                :key="x"
                :x="x"
                y="176"
                text-anchor="middle"
                font-size="13"
                fill="var(--text-mute)"
                font-family="monospace"
            >
                МЕСТО ФАРЫ
            </text>
        </template>
    </svg>
</template>
