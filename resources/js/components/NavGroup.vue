<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    icon?: string;
    label: string;
    collapsed: boolean;
    defaultOpen?: boolean;
}>();

const isOpen = ref(props.defaultOpen ?? false);
</script>

<template>
    <div class="space-y-0.5">
        <button
            @click="isOpen = !isOpen"
            class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-[12px] font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-700"
        >
            <svg
                v-if="icon === 'calculator'"
                class="h-4 w-4 shrink-0 text-slate-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                stroke-width="1.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                />
            </svg>
            <svg
                v-else-if="icon === 'search'"
                class="h-4 w-4 shrink-0 text-slate-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                stroke-width="1.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
            </svg>
            <svg
                v-else-if="icon === 'chart'"
                class="h-4 w-4 shrink-0 text-slate-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                stroke-width="1.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
            </svg>
            <span v-if="!collapsed" class="flex-1 truncate text-left">{{
                label
            }}</span>
            <svg
                v-if="!collapsed"
                class="h-3.5 w-3.5 text-slate-400 transition-transform"
                :class="{ 'rotate-90': isOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 5l7 7-7 7"
                />
            </svg>
        </button>
        <div
            v-if="isOpen && !collapsed"
            class="ml-4 space-y-0.5 border-l border-slate-200 pl-3"
        >
            <slot />
        </div>
    </div>
</template>
