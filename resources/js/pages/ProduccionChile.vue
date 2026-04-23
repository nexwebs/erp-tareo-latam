<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import Layout from '../components/Layout.vue';

const props = defineProps<{
    datos: any[];
    centros: any[];
    filtros: { fecha_inicio: string; fecha_fin: string };
    horaInicio: number;
    horaFin: number;
}>();

const fechaInicio = ref(props.filtros.fecha_inicio);
const fechaFin = ref(props.filtros.fecha_fin);
const busqueda = ref('');

const horas = computed(() => {
    const arr: number[] = [];
    for (let h = props.horaInicio; h <= props.horaFin; h++) arr.push(h);
    return arr;
});

function filtrar() {
    router.get(
        '/produccion/chile',
        { fecha_inicio: fechaInicio.value, fecha_fin: fechaFin.value },
        { preserveScroll: true },
    );
}

function exportar() {
    window.location.href = `/produccion/excel?fecha=${fechaFin.value}&pais=chile`;
}

const fmt = (v: number) => {
    const n = parseFloat(v) || 0;
    return n % 1 === 0
        ? n.toLocaleString('es-PE')
        : n.toLocaleString('es-PE', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
          });
};

const datosFiltrados = computed(() => {
    const q = busqueda.value.toLowerCase();
    if (!q) return props.datos;
    return props.datos.filter(
        (r) =>
            r.centro?.toLowerCase().includes(q) ||
            r.modelo?.toLowerCase().includes(q) ||
            r.serie?.toLowerCase().includes(q),
    );
});

const totalGeneral = computed(() =>
    datosFiltrados.value.reduce((s, r) => s + (parseFloat(r.total) || 0), 0),
);

const promedioGeneral = computed(() => {
    if (!datosFiltrados.value.length) return 0;
    const suma = datosFiltrados.value.reduce(
        (s, r) => s + (parseFloat(r.promedio) || 0),
        0,
    );
    return suma / datosFiltrados.value.length;
});

const totalesHora = computed(() => {
    const result: Record<number, number> = {};
    for (const h of horas.value) {
        result[h] = datosFiltrados.value.reduce(
            (s, r) => s + (parseFloat(r[`h${h}`]) || 0),
            0,
        );
    }
    return result;
});

const maxPorHora = computed(() => {
    const result: Record<number, number> = {};
    for (const h of horas.value) {
        result[h] = Math.max(
            ...props.datos.map((r) => parseFloat(r[`h${h}`]) || 0),
            0,
        );
    }
    return result;
});

function intensidad(valor: number, maxHora: number): string {
    if (!valor || !maxHora) return 'text-slate-700';
    const pct = valor / maxHora;
    if (pct >= 0.8) return 'text-sky-300 font-semibold';
    if (pct >= 0.5) return 'text-sky-400';
    if (pct >= 0.2) return 'text-sky-600';
    return 'text-slate-600';
}
</script>

<template>
    <Head title="Producción Chile" />
    <Layout>
        <div class="min-h-screen bg-[#080c18] font-sans text-white">
            <div class="mx-auto max-w-[1700px] px-4 py-6">
                <header
                    class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-semibold tracking-[0.2em] text-sky-500 uppercase"
                        >
                            Producción — Chile
                        </p>
                        <h1
                            class="text-3xl font-black tracking-tight text-white"
                        >
                            Reporte <span class="text-sky-400">por Centro</span>
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ filtros.fecha_inicio }} →
                            {{ filtros.fecha_fin }} ·
                            {{ datos.length }} máquinas
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            v-model="fechaInicio"
                            type="date"
                            class="rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none"
                        />
                        <input
                            v-model="fechaFin"
                            type="date"
                            class="rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none"
                        />
                        <button
                            @click="filtrar"
                            class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-sky-500 active:scale-95"
                        >
                            Filtrar
                        </button>
                        <button
                            @click="exportar"
                            class="rounded-lg border border-slate-600 bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:border-slate-400 hover:text-white active:scale-95"
                        >
                            ↓ Excel
                        </button>
                    </div>
                </header>

                <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-800/40 p-4"
                    >
                        <p
                            class="text-xs tracking-wider text-slate-500 uppercase"
                        >
                            Máquinas
                        </p>
                        <p class="mt-1 text-2xl font-black text-white">
                            {{ datos.length }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sky-900/50 bg-sky-900/20 p-4"
                    >
                        <p
                            class="text-xs tracking-wider text-sky-600 uppercase"
                        >
                            Total
                        </p>
                        <p class="mt-1 text-2xl font-black text-sky-400">
                            {{ fmt(totalGeneral) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-amber-900/50 bg-amber-900/20 p-4"
                    >
                        <p
                            class="text-xs tracking-wider text-amber-600 uppercase"
                        >
                            Promedio/hora
                        </p>
                        <p class="mt-1 text-2xl font-black text-amber-400">
                            {{ fmt(promedioGeneral) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-800/40 p-4"
                    >
                        <p
                            class="text-xs tracking-wider text-slate-500 uppercase"
                        >
                            Centros
                        </p>
                        <p class="mt-1 text-2xl font-black text-white">
                            {{ centros.length }}
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar centro, modelo o serie..."
                        class="w-full rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none sm:w-80"
                    />
                </div>

                <div
                    class="overflow-x-auto rounded-2xl border border-slate-800/80 bg-slate-900/60 pb-2 shadow-2xl"
                >
                    <table class="w-full min-w-[800px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-800">
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    #
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Modelo
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Centro
                                </th>
                                <th
                                    class="sticky left-[310px] z-20 border-r border-slate-800 bg-slate-900 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Serie
                                </th>
                                <th
                                    v-for="h in horas"
                                    :key="h"
                                    class="px-2 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    {{ h }}h
                                </th>
                                <th
                                    class="min-w-[70px] border-r border-slate-800 bg-sky-950/40 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-sky-500 uppercase"
                                >
                                    Total
                                </th>
                                <th
                                    class="min-w-[70px] bg-amber-950/40 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-amber-500 uppercase"
                                >
                                    Prom/h
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in datosFiltrados"
                                :key="row.item"
                                class="border-b border-slate-800/40 hover:bg-slate-800/30"
                            >
                                <td
                                    class="px-3 py-2 text-center text-xs text-slate-600"
                                >
                                    {{ row.item }}
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-300">
                                    {{ row.modelo }}
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-300">
                                    {{ row.centro }}
                                </td>
                                <td
                                    class="sticky left-[310px] z-10 border-r border-slate-800/50 bg-[#080c18] px-3 py-2 text-center font-mono text-xs text-cyan-400"
                                >
                                    {{ row.serie }}
                                </td>
                                <td
                                    v-for="h in horas"
                                    :key="h"
                                    class="px-2 py-2 text-center text-xs tabular-nums transition-colors"
                                    :class="
                                        intensidad(
                                            parseFloat(row[`h${h}`]) || 0,
                                            maxPorHora[h],
                                        )
                                    "
                                >
                                    {{ fmt(row[`h${h}`]) }}
                                </td>
                                <td
                                    class="min-w-[70px] border-r border-slate-800 bg-sky-950/20 px-3 py-2 text-right text-xs font-bold text-sky-400 tabular-nums"
                                >
                                    {{ fmt(row.total) }}
                                </td>
                                <td
                                    class="min-w-[70px] bg-amber-950/20 px-3 py-2 text-right text-xs font-bold text-amber-400 tabular-nums"
                                >
                                    {{ fmt(row.promedio) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr
                                class="border-t-2 border-slate-700 bg-slate-800/60"
                            >
                                <td
                                    colspan="4"
                                    class="sticky left-0 z-10 border-r border-slate-700 bg-slate-800 px-3 py-3 text-right text-xs font-black tracking-widest text-white uppercase"
                                >
                                    Total
                                </td>
                                <td
                                    v-for="h in horas"
                                    :key="h"
                                    class="border-r border-slate-700/50 px-2 py-3 text-center text-xs font-semibold text-sky-400 tabular-nums"
                                >
                                    {{ fmt(totalesHora[h]) }}
                                </td>
                                <td
                                    class="min-w-[70px] border-r border-slate-700 bg-sky-950/40 px-3 py-3 text-right text-sm font-black text-sky-300 tabular-nums"
                                >
                                    {{ fmt(totalGeneral) }}
                                </td>
                                <td
                                    class="min-w-[70px] bg-amber-950/40 px-3 py-3 text-right text-sm font-black text-amber-300 tabular-nums"
                                >
                                    {{ fmt(promedioGeneral) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <p
                        v-if="!datosFiltrados.length"
                        class="py-16 text-center text-slate-600"
                    >
                        Sin registros para los filtros aplicados.
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>
