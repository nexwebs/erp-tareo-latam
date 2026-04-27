<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Layout from '../components/Layout.vue';

const props = defineProps<{
    datos: any[];
    centros: any[];
    filtros: { fecha: string };
    horaInicio: number;
    horaFin: number;
}>();

const fecha = ref(props.filtros.fecha);
const busqueda = ref('');

const horas = computed(() => {
    const arr: number[] = [];
    for (let h = props.horaInicio; h <= props.horaFin; h++) arr.push(h);
    return arr;
});

function filtrar() {
    router.get('/produccion/australia', { fecha: fecha.value }, { preserveScroll: true });
}

function exportar() {
    const url = `/produccion/australia/pdf?fecha=${fecha.value}`;
    const printWindow = window.open(url, '_blank');
    printWindow?.addEventListener('load', () => {
        printWindow.document.title = `Producción Australia - ${fecha.value}`;
        printWindow.print();
        printWindow.matchMedia('print').addEventListener('change', (e) => {
            if (!e.matches) printWindow.close();
        });
    });
}

const fmt = (v: any) => {
    const n = parseFloat(v) || 0;
    if (n === 0) return n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return n.toLocaleString('es-PE', { maximumFractionDigits: 2 });
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
    return (
        datosFiltrados.value.reduce((s, r) => s + (parseFloat(r.promedio) || 0), 0) /
        datosFiltrados.value.length
    );
});

const totalesHora = computed(() => {
    const result: Record<number, number> = {};
    for (const h of horas.value) {
        result[h] = datosFiltrados.value.reduce((s, r) => s + (parseFloat(r[`h${h}`]) || 0), 0);
    }
    return result;
});

const maxPorHora = computed(() => {
    const result: Record<number, number> = {};
    for (const h of horas.value) {
        result[h] = Math.max(...props.datos.map((r) => parseFloat(r[`h${h}`]) || 0), 0);
    }
    return result;
});

const promedioMin = computed(() => {
    const valores = datosFiltrados.value.map((r) => parseFloat(r.promedio) || 0).filter((v) => v > 0);
    return valores.length ? Math.min(...valores) : 0;
});

const promedioMax = computed(() =>
    Math.max(...datosFiltrados.value.map((r) => parseFloat(r.promedio) || 0), 0),
);

function bgPromedio(promedio: any): string {
    const v = parseFloat(String(promedio)) || 0;
    if (!v || promedioMax.value === promedioMin.value) return 'background: transparent';
    const pct = (v - promedioMin.value) / (promedioMax.value - promedioMin.value);
    if (pct < 0.5) {
        const t = pct * 2;
        const r = 252, g = Math.round(165 + (211 - 165) * t), b = Math.round(165 + (6 - 165) * t);
        return `background: rgba(${r},${g},${b},0.28)`;
    }
    const t = (pct - 0.5) * 2;
    const r = Math.round(252 - (252 - 134) * t), g = Math.round(211 + (239 - 211) * t), b = Math.round(6 + (122 - 6) * t);
    return `background: rgba(${r},${g},${b},0.28)`;
}

function intensidad(valor: number, maxHora: number, transmitio: number): string {
    if (valor > 0) {
        const pct = valor / maxHora;
        if (pct >= 0.8) return 'text-green-500 font-semibold';
        if (pct >= 0.5) return 'text-green-600';
        if (pct >= 0.2) return 'text-green-700';
        return 'text-slate-500';
    }
    if (transmitio) return 'bg-blue-50 text-blue-400';
    return 'bg-red-50 text-red-400';
}

function claseTotal(row: any): string {
    const total = parseFloat(row.total) || 0;
    const promedio = parseFloat(row.promedio) || 0;
    if (total >= promedio) return 'bg-blue-50 text-blue-700 font-bold';
    return 'bg-red-50 text-red-500 font-bold';
}

const COL_W = { num: 40, modelo: 130, centro: 160, serie: 110 } as const;

const stickyLeft = {
    num: 0,
    modelo: COL_W.num,
    centro: COL_W.num + COL_W.modelo,
    serie: COL_W.num + COL_W.modelo + COL_W.centro,
} as const;
</script>

<template>
    <Head title="Producción Australia" />
    <Layout>
        <div class="min-h-screen bg-slate-50 font-sans text-slate-700">
            <div class="w-full px-2 py-4 sm:px-4 sm:py-6">
                <header class="mb-6 flex flex-col gap-3 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-1 text-xs font-semibold tracking-[0.2em] text-orange-500 uppercase">
                            Producción — Australia
                        </p>
                        <h1 class="text-2xl font-black tracking-tight text-slate-700 sm:text-3xl">
                            Reporte <span class="text-orange-500">por Centro</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            {{ filtros.fecha }} · {{ datos.length }} máquinas
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            v-model="fecha"
                            type="date"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-orange-500 focus:outline-none"
                        />
                        <button
                            @click="filtrar"
                            class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-orange-500 active:scale-95"
                        >
                            Filtrar
                        </button>
                        <button
                            @click="exportar"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-700 active:scale-95"
                        >
                            ↓ PDF
                        </button>
                    </div>
                </header>

                <div class="mb-4 grid grid-cols-2 gap-3 sm:mb-6 sm:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-slate-500 uppercase sm:text-xs">Máquinas</p>
                        <p class="mt-1 text-xl font-black text-slate-700 sm:text-2xl">{{ datos.length }}</p>
                    </div>
                    <div class="rounded-xl border border-orange-200 bg-orange-50 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-orange-600 uppercase sm:text-xs">Total</p>
                        <p class="mt-1 text-xl font-black text-orange-600 sm:text-2xl">{{ fmt(totalGeneral) }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-amber-600 uppercase sm:text-xs">Promedio/hora</p>
                        <p class="mt-1 text-xl font-black text-amber-500 sm:text-2xl">{{ fmt(promedioGeneral) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-slate-500 uppercase sm:text-xs">Centros</p>
                        <p class="mt-1 text-xl font-black text-slate-700 sm:text-2xl">{{ centros.length }}</p>
                    </div>
                </div>

                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar centro, modelo o serie..."
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-orange-500 focus:outline-none sm:w-80 sm:py-3"
                    />
                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs text-slate-500 w-fit">
                        <span class="font-semibold text-slate-600 whitespace-nowrap">Prom/h:</span>
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-3 w-8 rounded-sm" style="background: rgba(252,165,165,0.6)"></span>
                            <span class="whitespace-nowrap">Bajo (mín)</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-3 w-8 rounded-sm" style="background: rgba(252,211,6,0.45)"></span>
                            <span class="whitespace-nowrap">Medio</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-3 w-8 rounded-sm" style="background: rgba(134,239,122,0.5)"></span>
                            <span class="whitespace-nowrap">Alto (máx)</span>
                        </span>
                        <span class="hidden text-slate-400 sm:inline">· escala relativa al rango visible</span>
                    </div>
                </div>

                <div class="w-full overflow-x-auto rounded-2xl border border-slate-200 bg-white pb-2 shadow-sm">
                    <table class="w-full border-collapse text-sm" style="table-layout: auto;">
                        <colgroup>
                            <col :style="{ width: COL_W.num + 'px', minWidth: COL_W.num + 'px' }" />
                            <col :style="{ width: COL_W.modelo + 'px', minWidth: COL_W.modelo + 'px' }" />
                            <col :style="{ width: COL_W.centro + 'px', minWidth: COL_W.centro + 'px' }" />
                            <col :style="{ width: COL_W.serie + 'px', minWidth: COL_W.serie + 'px' }" />
                        </colgroup>
                        <thead>
                            <tr class="border-b border-slate-200 bg-white">
                                <th
                                    class="z-20 bg-white px-2 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.num + 'px' }"
                                >#</th>
                                <th
                                    class="z-20 bg-white px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.modelo + 'px' }"
                                >Modelo</th>
                                <th
                                    class="z-20 bg-white px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.centro + 'px' }"
                                >Centro</th>
                                <th
                                    class="z-20 border-r border-slate-200 bg-white px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.serie + 'px' }"
                                >Serie</th>
                                <th
                                    v-for="h in horas"
                                    :key="h"
                                    class="px-2 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase whitespace-nowrap"
                                >{{ h }}h</th>
                                <th class="bg-orange-50 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-orange-600 uppercase whitespace-nowrap">
                                    Total
                                </th>
                                <th class="bg-amber-50 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-amber-600 uppercase whitespace-nowrap">
                                    Prom/h
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="row in datosFiltrados"
                                :key="row.item"
                                class="border-b border-slate-100 hover:bg-slate-50"
                            >
                                <td
                                    class="z-10 bg-white px-2 py-2 text-center text-xs text-slate-500 whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.num + 'px' }"
                                >{{ row.item }}</td>
                                <td
                                    class="z-10 bg-white px-3 py-2 text-xs text-slate-600 whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.modelo + 'px' }"
                                >{{ row.modelo }}</td>
                                <td
                                    class="z-10 bg-white px-3 py-2 text-xs text-slate-600 whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.centro + 'px' }"
                                >{{ row.centro }}</td>
                                <td
                                    class="z-10 border-r border-slate-200 bg-white px-3 py-2 text-center font-mono text-xs text-orange-400 whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.serie + 'px' }"
                                >{{ row.serie }}</td>
                                <td
                                    v-for="h in horas"
                                    :key="h"
                                    class="px-2 py-2 text-center text-xs tabular-nums transition-colors whitespace-nowrap"
                                    :class="intensidad(parseFloat(row[`h${h}`]) || 0, maxPorHora[h], row[`trans_h${h}`] ?? 0)"
                                >{{ fmt(row[`h${h}`]) }}</td>
                                <td
                                    class="px-3 py-2 text-right text-xs tabular-nums whitespace-nowrap"
                                    :class="claseTotal(row)"
                                >{{ fmt(row.total) }}</td>
                                <td
                                    class="px-3 py-2 text-right text-xs tabular-nums whitespace-nowrap font-semibold text-slate-700 transition-colors"
                                    :style="bgPromedio(row.promedio)"
                                >{{ fmt(row.promedio) }}</td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="border-t-2 border-slate-200 bg-slate-50">
                                <td
                                    colspan="4"
                                    class="z-10 border-r border-slate-200 bg-slate-50 px-3 py-3 text-right text-xs font-black tracking-widest text-slate-600 uppercase whitespace-nowrap"
                                    :style="{ position: 'sticky', left: stickyLeft.num + 'px' }"
                                >Total</td>
                                <td
                                    v-for="h in horas"
                                    :key="h"
                                    class="border-r border-slate-100 px-2 py-3 text-center text-xs font-semibold text-orange-500 tabular-nums whitespace-nowrap"
                                >{{ fmt(totalesHora[h]) }}</td>
                                <td class="bg-orange-50 px-3 py-3 text-right text-sm font-black text-orange-600 tabular-nums whitespace-nowrap">
                                    {{ fmt(totalGeneral) }}
                                </td>
                                <td class="bg-amber-50 px-3 py-3 text-right text-sm font-black text-amber-500 tabular-nums whitespace-nowrap">
                                    {{ fmt(promedioGeneral) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <p v-if="!datosFiltrados.length" class="py-16 text-center text-slate-400">
                        Sin registros para los filtros aplicados.
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>