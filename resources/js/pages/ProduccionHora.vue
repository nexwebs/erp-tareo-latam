<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    datos: any[];
    filtros: { fecha: string; pais: string };
    horaInicio: number;
    horaFin: number;
}>();

const horas = computed(() => {
    const arr: number[] = [];
    for (let h = props.horaInicio; h <= props.horaFin; h++) arr.push(h);
    return arr;
});

const fecha = ref(props.filtros.fecha);
const pais  = ref(props.filtros.pais);

const paisNombre: Record<string, string> = {
    peru: 'Perú', chile: 'Chile', colombia: 'Colombia', australia: 'Australia',
};

function filtrar() {
    router.get('/produccion/hora', { fecha: fecha.value, pais: pais.value }, { preserveScroll: true });
}

function exportar() {
    window.location.href = `/produccion/exportar?fecha=${fecha.value}&pais=${pais.value}`;
}

const fmt = (v: number) =>
    new Intl.NumberFormat('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v || 0);

const totalGeneral = computed(() =>
    props.datos.reduce((s, r) => s + (parseFloat(r.total) || 0), 0),
);

const totalesHora = computed(() => {
    const result: Record<number, number> = {};
    for (const h of horas.value) {
        result[h] = props.datos.reduce((s, r) => s + (parseFloat(r[`h${h}`]) || 0), 0);
    }
    return result;
});

const promedioGeneral = computed(() => {
    if (!props.datos.length) return 0;
    const suma = props.datos.reduce((s, r) => s + (parseFloat(r.promedio) || 0), 0);
    return suma / props.datos.length;
});

const maxPorHora = computed(() => {
    const result: Record<number, number> = {};
    for (const h of horas.value) {
        result[h] = Math.max(...props.datos.map(r => parseFloat(r[`h${h}`]) || 0), 0);
    }
    return result;
});

function intensidad(valor: number, maxHora: number): string {
    if (!valor || !maxHora) return 'text-slate-700';
    const pct = valor / maxHora;
    if (pct >= 0.8) return 'text-emerald-300 font-semibold';
    if (pct >= 0.5) return 'text-emerald-400';
    if (pct >= 0.2) return 'text-emerald-600';
    return 'text-slate-600';
}
</script>

<template>
    <div class="min-h-screen bg-[#0a0e1a] font-sans text-white">
        <div class="mx-auto max-w-[1700px] px-4 py-6">

            <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-500">
                        Reporte por Hora — {{ paisNombre[pais] }}
                    </p>
                    <h1 class="text-3xl font-black tracking-tight text-white">
                        Producción <span class="text-cyan-400">{{ filtros.fecha }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ datos.length }} máquinas · horas {{ horaInicio }}:00 – {{ horaFin }}:00
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input
                        v-model="fecha"
                        type="date"
                        class="rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-sm text-white focus:border-cyan-500 focus:outline-none"
                    />
                    <select
                        v-model="pais"
                        class="rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-sm text-white focus:border-cyan-500 focus:outline-none"
                    >
                        <option value="peru">Perú</option>
                        <option value="chile">Chile</option>
                        <option value="colombia">Colombia</option>
                        <option value="australia">Australia</option>
                    </select>
                    <button
                        @click="filtrar"
                        class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-cyan-500 active:scale-95"
                    >
                        Filtrar
                    </button>
                    <button
                        @click="exportar"
                        class="rounded-lg border border-slate-600 bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:border-slate-400 hover:text-white active:scale-95"
                    >
                        ↓ CSV
                    </button>
                </div>
            </header>

            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl border border-slate-800 bg-slate-800/40 p-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Máquinas</p>
                    <p class="mt-1 text-2xl font-black text-white">{{ datos.length }}</p>
                </div>
                <div class="rounded-xl border border-emerald-900/50 bg-emerald-900/20 p-4">
                    <p class="text-xs text-emerald-600 uppercase tracking-wider">Total</p>
                    <p class="mt-1 text-2xl font-black text-emerald-400">{{ fmt(totalGeneral) }}</p>
                </div>
                <div class="rounded-xl border border-amber-900/50 bg-amber-900/20 p-4">
                    <p class="text-xs text-amber-600 uppercase tracking-wider">Promedio/hora</p>
                    <p class="mt-1 text-2xl font-black text-amber-400">{{ fmt(promedioGeneral) }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-800/40 p-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Horas activas</p>
                    <p class="mt-1 text-2xl font-black text-white">{{ horaFin - horaInicio + 1 }}h</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-800/80 bg-slate-900/60 shadow-2xl">
                <table class="w-full min-w-max border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800">
                            <th class="sticky left-0 z-20 border-r border-slate-800 bg-slate-900 px-3 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-500">#</th>
                            <th class="sticky left-10 z-20 border-r border-slate-800 bg-slate-900 px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">Modelo</th>
                            <th class="sticky left-[130px] z-20 border-r border-slate-800 bg-slate-900 px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">Centro</th>
                            <th class="sticky left-[310px] z-20 border-r border-slate-800 bg-slate-900 px-3 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-500">Serie</th>
                            <th
                                v-for="h in horas"
                                :key="h"
                                class="border-r border-slate-800/50 px-2 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-500"
                            >
                                {{ h }}h
                            </th>
                            <th class="border-r border-slate-800 bg-emerald-950/40 px-3 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-emerald-500">Total</th>
                            <th class="bg-amber-950/40 px-3 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-amber-500">Prom/h</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in datos"
                            :key="row.item"
                            class="border-b border-slate-800/40 transition-colors hover:bg-slate-800/30"
                        >
                            <td class="sticky left-0 z-10 border-r border-slate-800/50 bg-[#0a0e1a] px-3 py-2 text-center text-xs text-slate-600">{{ row.item }}</td>
                            <td class="sticky left-10 z-10 w-[120px] max-w-[120px] truncate border-r border-slate-800/50 bg-[#0a0e1a] px-3 py-2 text-xs text-slate-300">{{ row.modelo }}</td>
                            <td class="sticky left-[130px] z-10 w-[180px] max-w-[180px] truncate border-r border-slate-800/50 bg-[#0a0e1a] px-3 py-2 text-xs text-slate-300">{{ row.centro }}</td>
                            <td class="sticky left-[310px] z-10 border-r border-slate-800/50 bg-[#0a0e1a] px-3 py-2 text-center font-mono text-xs text-cyan-400">{{ row.serie }}</td>
                            <td
                                v-for="h in horas"
                                :key="h"
                                class="border-r border-slate-800/30 px-2 py-2 text-center text-xs tabular-nums transition-colors"
                                :class="intensidad(parseFloat(row[`h${h}`]) || 0, maxPorHora[h])"
                            >
                                {{ fmt(row[`h${h}`]) }}
                            </td>
                            <td class="border-r border-slate-800 bg-emerald-950/20 px-3 py-2 text-right text-xs font-bold tabular-nums text-emerald-400">{{ fmt(row.total) }}</td>
                            <td class="bg-amber-950/20 px-3 py-2 text-right text-xs font-bold tabular-nums text-amber-400">{{ fmt(row.promedio) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-700 bg-slate-800/60">
                            <td colspan="4" class="sticky left-0 z-10 border-r border-slate-700 bg-slate-800 px-3 py-3 text-right text-xs font-black uppercase tracking-widest text-white">Total</td>
                            <td
                                v-for="h in horas"
                                :key="h"
                                class="border-r border-slate-700/50 px-2 py-3 text-center text-xs font-semibold tabular-nums text-emerald-400"
                            >
                                {{ fmt(totalesHora[h]) }}
                            </td>
                            <td class="border-r border-slate-700 bg-emerald-950/40 px-3 py-3 text-right text-sm font-black tabular-nums text-emerald-300">{{ fmt(totalGeneral) }}</td>
                            <td class="bg-amber-950/40 px-3 py-3 text-right text-sm font-black tabular-nums text-amber-300">{{ fmt(promedioGeneral) }}</td>
                        </tr>
                    </tfoot>
                </table>
                <p v-if="!datos.length" class="py-16 text-center text-slate-600">Sin registros para la fecha y país seleccionados.</p>
            </div>
        </div>
    </div>
</template>