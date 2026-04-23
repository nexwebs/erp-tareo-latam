<script setup lang="ts">
defineProps<{
    href: string;
    active: boolean;
}>();
</script>

<template>
    <a
        :href="href"
        class="flex items-center gap-2 rounded-md px-3 py-2 text-[11.5px] font-medium transition-all duration-200"
        :class="
            active
                ? 'bg-slate-800/50 text-emerald-400'
                : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200'
        "
    >
        <span
            class="h-1.5 w-1.5 shrink-0 rounded-full"
            :class="active ? 'bg-emerald-500' : 'bg-slate-600'"
        ></span>
        <span class="truncate"><slot /></span>
    </a>
</template>
