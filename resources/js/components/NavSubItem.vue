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
                ? 'bg-emerald-50 text-emerald-600'
                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'
        "
    >
        <span
            class="h-1.5 w-1.5 shrink-0 rounded-full"
            :class="active ? 'bg-emerald-500' : 'bg-slate-400'"
        ></span>
        <span class="truncate"><slot /></span>
    </a>
</template>
