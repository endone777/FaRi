<script setup lang="ts" generic="T extends string | number">
import { computed, nextTick, onBeforeUnmount, ref, useId, watch } from 'vue';

export type FariOption<V> = {
    value: V;
    label: string;
    hint?: string;
    group?: string;
};

const {
    options,
    placeholder = 'Выберите',
    searchPlaceholder = 'Начните вводить',
    emptyText = 'Ничего не найдено',
    disabled = false,
    clearable = false,
    invalid = false,
    id,
} = defineProps<{
    options: FariOption<T>[];
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: string;
    disabled?: boolean;
    clearable?: boolean;
    invalid?: boolean;
    id?: string;
}>();

const selected = defineModel<T | null>({ default: null });

const uid = useId();
const listId = computed(() => `fari-select-${uid}`);

const open = ref(false);
const query = ref('');
const active = ref(0);
const root = ref<HTMLElement | null>(null);
const panel = ref<HTMLElement | null>(null);
const search = ref<HTMLInputElement | null>(null);
const list = ref<HTMLElement | null>(null);

/**
 * The panel is teleported to the body and placed by hand, so containers with
 * `overflow: hidden` — the picker bar, the admin toolbar — cannot clip it.
 */
const position = ref({ top: 0, left: 0, width: 0, flipped: false });

const panelStyle = computed(() => ({
    top: `${position.value.top}px`,
    left: `${position.value.left}px`,
    width: `${position.value.width}px`,
}));

const PANEL_MAX_HEIGHT = 320;
const GAP = 6;

function place(): void {
    const trigger = root.value?.querySelector('.fsel-trigger');

    if (!trigger) {
        return;
    }

    const rect = trigger.getBoundingClientRect();
    const below = window.innerHeight - rect.bottom;
    const height = Math.min(
        PANEL_MAX_HEIGHT,
        panel.value?.offsetHeight ?? PANEL_MAX_HEIGHT,
    );
    const flipped = below < height + GAP && rect.top > below;

    position.value = {
        top: flipped ? rect.top - height - GAP : rect.bottom + GAP,
        left: rect.left,
        width: rect.width,
        flipped,
    };
}

function onViewportChange(): void {
    if (open.value) {
        place();
    }
}

function watchViewport(): void {
    window.addEventListener('scroll', onViewportChange, true);
    window.addEventListener('resize', onViewportChange);
}

function unwatchViewport(): void {
    window.removeEventListener('scroll', onViewportChange, true);
    window.removeEventListener('resize', onViewportChange);
}

function onPointerDown(event: PointerEvent): void {
    const target = event.target as Node | null;

    if (
        target &&
        !root.value?.contains(target) &&
        !panel.value?.contains(target)
    ) {
        close();
    }
}

onBeforeUnmount(() => {
    unwatchViewport();
    document.removeEventListener('pointerdown', onPointerDown, true);
});

const current = computed(() =>
    options.find((option) => option.value === selected.value),
);

const filtered = computed(() => {
    const needle = query.value.trim().toLowerCase();

    if (!needle) {
        return options;
    }

    return options.filter((option) =>
        `${option.label} ${option.hint ?? ''} ${option.group ?? ''}`
            .toLowerCase()
            .includes(needle),
    );
});

/** Options in display order, with a heading before each new group. */
const rows = computed(() => {
    const out: (
        | { kind: 'group'; label: string }
        | { kind: 'option'; option: FariOption<T>; index: number }
    )[] = [];
    let group: string | undefined;

    filtered.value.forEach((option, index) => {
        if (option.group && option.group !== group) {
            group = option.group;
            out.push({ kind: 'group', label: option.group });
        }

        out.push({ kind: 'option', option, index });
    });

    return out;
});

function toggle(): void {
    if (disabled) {
        return;
    }

    if (open.value) {
        close();

        return;
    }

    show();
}

function show(): void {
    open.value = true;
    query.value = '';
    active.value = Math.max(
        0,
        filtered.value.findIndex((option) => option.value === selected.value),
    );

    watchViewport();
    document.addEventListener('pointerdown', onPointerDown, true);

    void nextTick(() => {
        place();
        search.value?.focus();
        scrollToActive();
    });
}

function close(): void {
    if (!open.value) {
        return;
    }

    open.value = false;
    unwatchViewport();
    document.removeEventListener('pointerdown', onPointerDown, true);
}

function pick(option: FariOption<T>): void {
    selected.value = option.value;
    close();
}

function clear(): void {
    selected.value = null;
    close();
}

function move(step: number): void {
    const total = filtered.value.length;

    if (total === 0) {
        return;
    }

    active.value = (active.value + step + total) % total;
    scrollToActive();
}

function scrollToActive(): void {
    void nextTick(() => {
        list.value
            ?.querySelector<HTMLElement>('[data-active="true"]')
            ?.scrollIntoView({ block: 'nearest' });
    });
}

function onKeydown(event: KeyboardEvent): void {
    if (!open.value) {
        if (['ArrowDown', 'Enter', ' '].includes(event.key)) {
            event.preventDefault();
            show();
        }

        return;
    }

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            move(1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            move(-1);
            break;
        case 'Home':
            event.preventDefault();
            active.value = 0;
            scrollToActive();
            break;
        case 'End':
            event.preventDefault();
            active.value = filtered.value.length - 1;
            scrollToActive();
            break;
        case 'Enter': {
            event.preventDefault();
            const option = filtered.value[active.value];

            if (option) {
                pick(option);
            }

            break;
        }
        case 'Escape':
            event.preventDefault();
            close();
            break;
    }
}

function onFocusOut(event: FocusEvent): void {
    const next = event.relatedTarget as Node | null;

    if (next && (root.value?.contains(next) || panel.value?.contains(next))) {
        return;
    }

    close();
}

watch(query, () => {
    active.value = 0;
    void nextTick(place);
});

watch(
    () => options,
    () => {
        if (
            selected.value !== null &&
            !options.some((option) => option.value === selected.value)
        ) {
            selected.value = null;
        }
    },
);
</script>

<template>
    <div
        ref="root"
        class="fsel"
        :class="{
            'fsel-open': open,
            'fsel-off': disabled,
            'fsel-bad': invalid,
        }"
        @focusout="onFocusOut"
        @keydown="onKeydown"
    >
        <button
            :id="id"
            class="fsel-trigger"
            type="button"
            role="combobox"
            :aria-expanded="open"
            :aria-controls="listId"
            :disabled="disabled"
            @click="toggle"
        >
            <span v-if="current" class="fsel-value">
                {{ current.label }}
                <small v-if="current.hint">{{ current.hint }}</small>
            </span>
            <span v-else class="fsel-placeholder">{{ placeholder }}</span>

            <span
                v-if="clearable && current && !disabled"
                class="fsel-clear"
                role="button"
                tabindex="-1"
                aria-label="Очистить"
                @click.stop="clear"
            >
                ×
            </span>
            <span class="fsel-caret" aria-hidden="true" />
        </button>

        <Teleport to="body">
            <div
                v-if="open"
                ref="panel"
                class="fsel-panel"
                :class="{ 'fsel-panel-up': position.flipped }"
                :style="panelStyle"
                @focusout="onFocusOut"
                @keydown="onKeydown"
            >
                <div class="fsel-search">
                    <input
                        ref="search"
                        v-model="query"
                        type="text"
                        autocomplete="off"
                        spellcheck="false"
                        :placeholder="searchPlaceholder"
                        :aria-controls="listId"
                    />
                </div>

                <div :id="listId" ref="list" class="fsel-list" role="listbox">
                    <p v-if="filtered.length === 0" class="fsel-empty">
                        {{ emptyText }}
                    </p>

                    <template v-for="(row, index) in rows" :key="index">
                        <p v-if="row.kind === 'group'" class="fsel-group">
                            {{ row.label }}
                        </p>

                        <button
                            v-else
                            class="fsel-option"
                            type="button"
                            role="option"
                            tabindex="-1"
                            :data-active="active === row.index"
                            :aria-selected="selected === row.option.value"
                            @click="pick(row.option)"
                            @mousemove="active = row.index"
                        >
                            <span class="fsel-option-label">
                                {{ row.option.label }}
                            </span>
                            <span
                                v-if="row.option.hint"
                                class="fsel-option-hint"
                            >
                                {{ row.option.hint }}
                            </span>
                        </button>
                    </template>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.fsel {
    position: relative;
}

.fsel-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 42px;
    padding: 8px 12px;
    text-align: start;
    border: 1px solid var(--line);
    border-radius: var(--r-md);
    background: var(--surface);
    color: var(--text);
    cursor: pointer;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.fsel-trigger:hover:not(:disabled) {
    border-color: var(--xenon);
}

.fsel-open .fsel-trigger {
    border-color: var(--xenon);
    box-shadow: 0 0 0 3px var(--xenon-wash);
}

.fsel-bad .fsel-trigger {
    border-color: var(--bad);
}

.fsel-off .fsel-trigger {
    cursor: not-allowed;
    color: var(--text-mute);
    background: var(--surface-2);
}

.fsel-value {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: baseline;
    gap: 8px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.fsel-value small {
    font-family: var(--f-data);
    font-size: 0.68rem;
    color: var(--text-mute);
}

.fsel-placeholder {
    flex: 1;
    color: var(--text-mute);
}

.fsel-clear {
    font-size: 1.1rem;
    line-height: 1;
    color: var(--text-mute);
    padding-inline: 2px;
    cursor: pointer;
}

.fsel-clear:hover {
    color: var(--bad);
}

.fsel-caret {
    width: 0;
    height: 0;
    border-inline: 5px solid transparent;
    border-block-start: 6px solid var(--text-mute);
    transition: transform 0.15s ease;
}

.fsel-open .fsel-caret {
    transform: rotate(180deg);
}

/* Teleported to the body, so it restates the base type and box sizing. */
.fsel-panel {
    position: fixed;
    z-index: 120;
    box-sizing: border-box;
    font-family: var(--f-body);
    font-size: var(--step-0);
    line-height: 1.6;
    color: var(--text);
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--r-md);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.fsel-panel *,
.fsel-panel *::before,
.fsel-panel *::after {
    box-sizing: border-box;
}

.fsel-panel input,
.fsel-panel button {
    font: inherit;
    color: inherit;
}

.fsel-search {
    padding: 8px;
    border-bottom: 1px solid var(--line-soft);
    background: var(--surface-2);
}

.fsel-search input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid var(--line);
    border-radius: var(--r-sm);
    background: var(--surface);
}

.fsel-list {
    max-height: 264px;
    overflow-y: auto;
    padding: 6px;
}

.fsel-group {
    padding: 8px 10px 4px;
    font-family: var(--f-data);
    font-size: 0.66rem;
    letter-spacing: 0.11em;
    text-transform: uppercase;
    color: var(--text-mute);
}

.fsel-option {
    width: 100%;
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 10px;
    border: 0;
    border-radius: var(--r-sm);
    background: transparent;
    color: inherit;
    text-align: start;
    cursor: pointer;
}

.fsel-option[data-active='true'] {
    background: var(--surface-2);
}

.fsel-option[aria-selected='true'] {
    background: var(--xenon-wash);
    color: var(--xenon-ink);
    font-weight: 600;
}

.fsel-option-hint {
    font-family: var(--f-data);
    font-size: 0.68rem;
    color: var(--text-mute);
    white-space: nowrap;
}

.fsel-empty {
    padding: 14px 10px;
    text-align: center;
    color: var(--text-mute);
    font-size: var(--step--1);
}
</style>
