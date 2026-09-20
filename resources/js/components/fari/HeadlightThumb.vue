<script setup lang="ts">
import { computed, useId } from 'vue';
import { beamColor, SHAPES } from '@/lib/fari';

const { shape = 'sharp', colorTemp = 5000, label = 'Схема фары' } =
    defineProps<{
        shape?: string;
        colorTemp?: number;
        label?: string;
    }>();

const uid = useId();
const glow = computed(() => beamColor(colorTemp));
const path = computed(() => SHAPES[shape] ?? SHAPES.sharp);
</script>

<template>
    <svg viewBox="0 0 320 180" role="img" :aria-label="label">
        <defs>
            <radialGradient :id="`grad-${uid}`" cx="36%" cy="40%">
                <stop offset="0%" stop-color="#fff" />
                <stop offset="48%" :stop-color="glow" />
                <stop offset="100%" :stop-color="glow" stop-opacity=".35" />
            </radialGradient>
            <filter
                :id="`blur-${uid}`"
                x="-50%"
                y="-50%"
                width="200%"
                height="200%"
            >
                <feGaussianBlur stdDeviation="11" />
            </filter>
        </defs>
        <ellipse
            cx="150"
            cy="96"
            rx="104"
            ry="44"
            :fill="glow"
            opacity=".22"
            :filter="`url(#blur-${uid})`"
        />
        <path
            :d="path"
            transform="translate(-83.6,-126.9) scale(1.34)"
            :fill="`url(#grad-${uid})`"
            :stroke="glow"
            stroke-width="2"
            vector-effect="non-scaling-stroke"
        />
        <text
            x="300"
            y="164"
            text-anchor="end"
            font-size="12"
            font-family="monospace"
            fill="var(--text-mute)"
        >
            {{ colorTemp }}K
        </text>
    </svg>
</template>
