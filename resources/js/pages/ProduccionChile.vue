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
    tipoCambio: number | null;
}>();

const fechaInicio = ref(props.filtros.fecha_inicio);
const fechaFin = ref(props.filtros.fecha_fin);
const busqueda = ref('');
const tipoCambioManual = ref<number | null>(props.tipoCambio);

const tipoCambioApi = computed(() => props.tipoCambio);
const tipoCambioActivo = computed(() => tipoCambioManual.value ?? props.tipoCambio);
const tipoCambioOrigen = computed(() => tipoCambioManual.value ? 'manual' : 'api');

const actualizarTipoCambio = () => {
    if (tipoCambioManual.value && tipoCambioManual.value > 0) {
        router.get(
            '/produccion/chile',
            {
                fecha_inicio: fechaInicio.value,
                fecha_fin: fechaFin.value,
                tipo_cambio: tipoCambioManual.value,
            },
            { preserveScroll: true },
        );
    }
};

const horas = computed(() => {
    const arr: number[] = [];
    for (let h = props.horaInicio; h <= props.horaFin; h++) arr.push(h);
    return arr;
});

function filtrar() {
    router.get(
        '/produccion/chile',
        {
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
            ...(tipoCambioManual.value ? { tipo_cambio: tipoCambioManual.value } : {}),
        },
        { preserveScroll: true },
    );
}

const fmt = (v: any) => {
    const n = parseFloat(v) || 0;
    return n % 1 === 0
        ? n.toLocaleString('es-PE')
        : n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const convertir = (v: any): number => {
    const tc = tipoCambioActivo.value;
    return tc ? (parseFloat(v) || 0) * tc : parseFloat(v) || 0;
};

const fmtTC = (v: any) => fmt(convertir(v));

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

const statsPromedio = computed(() => {
    const values = props.datos.map((r) => parseFloat(r.promedio) || 0).filter((v) => v > 0);
    if (!values.length) return { media: 0 };
    const media = values.reduce((a, b) => a + b, 0) / values.length;
    return { media };
});

function colorPromedio(promedio: number): string {
    const v = promedio || 0;
    const { media } = statsPromedio.value;
    if (!media) return 'text-slate-400';
    if (v >= media) return 'text-emerald-400';
    if (v >= media * 0.7) return 'text-yellow-400';
    return 'text-rose-400';
}

function exportar() {
    const tc = tipoCambioActivo.value;
    const conv = (v: any) => (tc ? (parseFloat(v) || 0) * tc : parseFloat(v) || 0);
    const n = (v: any) => conv(v).toFixed(2);
    const horasArr = horas.value;

    const th = (t: string) => `<th style="background:#1e3a5f;color:#7dd3fc;padding:6px 10px;border:1px solid #334155;white-space:nowrap">${t}</th>`;
    const td = (t: any, extra = '') => `<td style="padding:5px 10px;border:1px solid #1e293b;${extra}">${t}</td>`;

    const encabezado = `<tr>
        ${th('#')}${th('Modelo')}${th('Centro')}${th('Serie')}
        ${horasArr.map(h => th(`${h}h`)).join('')}
        ${th('Total')}${th('Prom/h')}
    </tr>`;

    const filas = datosFiltrados.value.map(r => `<tr>
        ${td(r.item)}${td(r.modelo)}${td(r.centro)}
        ${td(r.serie, 'font-family:monospace;color:#22d3ee')}
        ${horasArr.map(h => td(n(r[`h${h}`]), 'text-align:right')).join('')}
        ${td(n(r.total), 'text-align:right;font-weight:bold;color:#38bdf8')}
        ${td(n(r.promedio), 'text-align:right;font-weight:bold')}
    </tr>`).join('');

    const totales = `<tr style="background:#1e293b;font-weight:bold">
        ${td('')}${td('')}${td('')}
        ${td('TOTAL', 'font-weight:bold')}
        ${horasArr.map(h => td(n(totalesHora.value[h]), 'text-align:right;color:#38bdf8')).join('')}
        ${td(n(totalGeneral.value), 'text-align:right;color:#7dd3fc;font-size:1.05em')}
        ${td(n(promedioGeneral.value), 'text-align:right;color:#fbbf24;font-size:1.05em')}
    </tr>`;

    const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:x="urn:schemas-microsoft-com:office:excel"
        xmlns="http://www.w3.org/TR/REC-html40">
    <head><meta charset="utf-8">
    <style>
        body{font-family:Arial,sans-serif;font-size:11px;background:#0f172a;color:#e2e8f0}
        table{border-collapse:collapse;width:100%}
    </style></head>
    <body>
        <h3 style="color:#7dd3fc;margin-bottom:8px">
            Producción Chile · ${fechaInicio.value} → ${fechaFin.value}
            ${tc ? ` · TC: ${tc}` : ''}
        </h3>
        <table>${encabezado}${filas}${totales}</table>
    </body></html>`;

    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `produccion_chile_${fechaInicio.value}_${fechaFin.value}${tc ? `_TC${tc}` : ''}.xls`;
    a.click();
    URL.revokeObjectURL(url);
}

const COL_W = { serie: 110 } as const;
const serieLeft = 330;
</script>

<template>
    <Head title="Producción Chile" />
    <Layout>
        <div class="min-h-screen bg-[#080c18] font-sans text-white">
            <div class="mx-auto max-w-[1700px] px-2 py-4 sm:px-4 sm:py-6">
                <header class="mb-6 flex flex-col gap-3 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-1 text-xs font-semibold tracking-[0.2em] text-sky-500 uppercase">
                            Producción — Chile
                        </p>
                        <h1 class="text-2xl font-black tracking-tight text-white sm:text-3xl">
                            Reporte <span class="text-sky-400">por Centro</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            {{ filtros.fecha_inicio }} → {{ filtros.fecha_fin }} ·
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
                        <div class="flex items-center gap-2 rounded-lg border border-slate-600 bg-slate-800 px-3 py-2">
                            <span class="text-xs text-slate-500">TC:</span>
                            <input
                                v-model="tipoCambioManual"
                                type="number"
                                step="0.0001"
                                class="w-20 bg-transparent text-sm text-white focus:outline-none"
                                @blur="actualizarTipoCambio"
                            />
                            <span v-if="tipoCambioOrigen === 'manual'" class="text-[10px] text-amber-400" title="Tipo de cambio manual">✎</span>
                            <span v-else-if="tipoCambioApi" class="text-[10px] text-emerald-400" title="Tipo de cambio API">✓</span>
                        </div>
                    </div>
                </header>

                <div class="mb-4 grid grid-cols-2 gap-3 sm:mb-6 sm:grid-cols-4">
                    <div class="rounded-xl border border-slate-800 bg-slate-800/40 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-slate-500 uppercase sm:text-xs">Máquinas</p>
                        <p class="mt-1 text-xl font-black text-white sm:text-2xl">{{ datos.length }}</p>
                    </div>
                    <div class="rounded-xl border border-sky-900/50 bg-sky-900/20 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-sky-600 uppercase sm:text-xs">Total</p>
                        <p class="mt-1 text-xl font-black text-sky-400 sm:text-2xl">{{ fmtTC(totalGeneral) }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-900/50 bg-amber-900/20 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-amber-600 uppercase sm:text-xs">Promedio/hora</p>
                        <p class="mt-1 text-xl font-black text-amber-400 sm:text-2xl">{{ fmtTC(promedioGeneral) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-800/40 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-slate-500 uppercase sm:text-xs">Centros</p>
                        <p class="mt-1 text-xl font-black text-white sm:text-2xl">{{ centros.length }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar centro, modelo o serie..."
                        class="w-full rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none sm:w-80 sm:py-3"
                    />
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-800/80 bg-slate-900/60 pb-2 shadow-2xl">
                    <table class="w-full border-collapse text-sm">
                        <colgroup>
                            <col :style="{ width: '40px', minWidth: '40px' }" />
                            <col :style="{ width: '130px', minWidth: '130px' }" />
                            <col :style="{ width: '160px', minWidth: '160px' }" />
                            <col :style="{ width: COL_W.serie + 'px', minWidth: COL_W.serie + 'px' }" />
                        </colgroup>

                        <thead>
                            <tr class="border-b border-slate-800">
                                <th class="px-2 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">#</th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase">Modelo</th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase">Centro</th>
                                <th
                                    class="z-20 border-r border-slate-800 bg-slate-900 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                    :style="{ position: 'sticky', left: serieLeft + 'px' }"
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
                                <th class="min-w-[80px] border-r border-slate-800 bg-sky-950/40 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-sky-500 uppercase">Total</th>
                                <th class="min-w-[80px] bg-amber-950/40 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-amber-500 uppercase">Prom/h</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="row in datosFiltrados"
                                :key="row.item"
                                class="border-b border-slate-800/40 hover:bg-slate-800/30"
                            >
                                <td class="px-2 py-2 text-center text-xs text-slate-600">{{ row.item }}</td>
                                <td class="px-3 py-2 text-xs text-slate-300">{{ row.modelo }}</td>
                                <td class="px-3 py-2 text-xs text-slate-300">{{ row.centro }}</td>
                                <td
                                    class="z-10 border-r border-slate-800/50 bg-[#080c18] px-3 py-2 text-center font-mono text-xs text-cyan-400"
                                    :style="{ position: 'sticky', left: serieLeft + 'px' }"
                                >
                                    {{ row.serie }}
                                </td>
                                <td
                                    v-for="h in horas"
                                    :key="h"
                                    class="px-2 py-2 text-center text-xs tabular-nums transition-colors"
                                    :class="intensidad(parseFloat(row[`h${h}`]) || 0, maxPorHora[h])"
                                >
                                    {{ fmtTC(row[`h${h}`]) }}
                                </td>
                                <td class="min-w-[80px] border-r border-slate-800 bg-sky-950/20 px-3 py-2 text-right text-xs font-bold text-sky-400 tabular-nums">
                                    {{ fmtTC(row.total) }}
                                </td>
                                <td
                                    class="min-w-[80px] bg-amber-950/20 px-3 py-2 text-right text-xs font-bold tabular-nums"
                                    :class="colorPromedio(row.promedio)"
                                >
                                    {{ fmtTC(row.promedio) }}
                                </td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="border-t-2 border-slate-700 bg-slate-800/60">
                                <td colspan="3" class="px-3 py-3 text-right text-xs font-black tracking-widest text-white uppercase">Total</td>
                                <td
                                    class="border-r border-slate-700 bg-slate-800 px-3 py-3 text-right text-xs font-black text-white uppercase"
                                    :style="{ position: 'sticky', left: '40px' }"
                                ></td>
                                <td
                                    v-for="h in horas"
                                    :key="h"
                                    class="border-r border-slate-700/50 px-2 py-3 text-center text-xs font-semibold text-sky-400 tabular-nums"
                                >
                                    {{ fmtTC(totalesHora[h]) }}
                                </td>
                                <td class="min-w-[80px] border-r border-slate-700 bg-sky-950/40 px-3 py-3 text-right text-sm font-black text-sky-300 tabular-nums">
                                    {{ fmtTC(totalGeneral) }}
                                </td>
                                <td class="min-w-[80px] bg-amber-950/40 px-3 py-3 text-right text-sm font-black text-amber-300 tabular-nums">
                                    {{ fmtTC(promedioGeneral) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <p v-if="!datosFiltrados.length" class="py-16 text-center text-slate-600">
                        Sin registros para los filtros aplicados.
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>