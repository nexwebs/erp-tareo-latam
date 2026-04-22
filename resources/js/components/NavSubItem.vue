<script setup lang="ts">
defineProps<{
    href: string;
    active: boolean;
}>();
</script>

<template>
    <a
        :href="href"
        class="flex items-center gap-2 rounded-md px-3 py-2 text-[12.5px] font-medium transition-all duration-200"
        :class="[
            active
                ? 'bg-emerald-50 font-semibold text-emerald-700'
                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700',
            href === '#' ? 'cursor-not-allowed opacity-50' : '',
        ]"
    >
        <span
            class="h-1 w-1 shrink-0 rounded-full"
            :class="active ? 'bg-emerald-500' : 'bg-slate-300'"
        ></span>
        <span class="truncate"><slot /></span>
    </a>
</template>
