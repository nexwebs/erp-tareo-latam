<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import Layout from '../components/Layout.vue';

const props = defineProps<{
    datos: any[];
    centros: any[];
    totales: any;
    filtros: { fecha: string; pais: string };
    horaInicio: number;
    horaFin: number;
}>();

const paisNombre: Record<string, string> = {
    cl: 'Chile',
    co: 'Colombia',
    au: 'Australia',
    pe: 'Perú',
    todos: 'Todos',
};

const pais = computed(() => props.filtros.pais);
const fecha = computed(() => props.filtros.fecha);
const paisActual = computed(() => paisNombre[pais.value] || pais.value);

const busqueda = ref('');

const horas = computed(() => {
    const arr: number[] = [];
    for (let h = props.horaInicio; h <= props.horaFin; h++) arr.push(h);
    return arr;
});

function filtrar() {
    router.get(
        '/produccion/controlar',
        { fecha: fecha.value, pais: pais.value },
        { preserveScroll: true },
    );
}

const fmt = (v: any) => {
    const n = parseFloat(v) || 0;
    return n % 1 === 0
        ? n.toLocaleString('es-PE')
        : n.toLocaleString('es-PE', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
          });
};

const fmtInt = (v: any) =>
    Math.round(parseFloat(v) || 0).toLocaleString('es-PE');

const datosFiltrados = computed(() => {
    const q = busqueda.value.toLowerCase();
    if (!q) return props.datos;
    return props.datos.filter(
        (r) =>
            r.NombreCentro?.toLowerCase().includes(q) ||
            r.Modelo?.toLowerCase().includes(q) ||
            r.CodigoMaquina?.toLowerCase().includes(q),
    );
});

const totalMostrado = computed(() =>
    datosFiltrados.value.reduce((s, r) => s + (parseFloat(r.total) || 0), 0),
);

const promedioMostrado = computed(() => {
    if (!datosFiltrados.value.length) return 0;
    return (
        datosFiltrados.value.reduce(
            (s, r) => s + (parseFloat(r.promedio) || 0),
            0,
        ) / datosFiltrados.value.length
    );
});

const statsPromedio = computed(() => {
    const values = props.datos
        .map((r) => parseFloat(r.promedio) || 0)
        .filter((v) => v > 0);
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
    const th = (t: string) => `
<th
    style="
        background: #4c1d95;
        color: #c4b5fd;
        padding: 6px 10px;
        border: 1px solid #334155;
        white-space: nowrap;
    "
>${t}</th>
`;
    const td = (t: any, extra = '') => `
<td style="padding:5px 10px;border:1px solid #1e293b;${extra}">${t}</td>
`;
    const encabezado = `
<tr>${th('#')}${th('Centro')}${th('Modelo')}${th('Serie')}${th('Registros')}${th('Promedio/h')}${th('Total')}</tr>
`;
    const filas = datosFiltrados.value
        .map(
            (r) => `
<tr>${td(r.item)}${td(r.NombreCentro)}${td(r.Modelo)}${td(r.CodigoMaquina, 'font-family:monospace;color:#22d3ee')}${td(fmtInt(r.conteo), 'text-align:center')}${td(fmt(r.promedio), 'text-align:right;font-weight:bold;color:#fbbf24')}${td(fmt(r.total), 'text-align:right;font-weight:bold;color:#34d399')}</tr>
`,
        )
        .join('');
    const totales = `
<tr
    style="background: #1e293b; font-weight: bold"
>${td('TOTALES', 'font-weight:bold')}${td('')}${td('')}${td('')}${td(fmtInt(datosFiltrados.value.reduce((s, r) => s + (parseFloat(r.conteo) || 0), 0)), 'text-align:center;color:#c4b5fd')}${td(fmt(promedioMostrado.value), 'text-align:right;color:#fbbf24;font-size:1.05em')}${td(fmt(totalMostrado.value), 'text-align:right;color:#34d399;font-size:1.05em')}</tr>
`;
    const html = `
<html
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel"
    xmlns="http://www.w3.org/TR/REC-html40"
>
    <head>
        <meta charset="utf-8" />
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 11px;
                background: #0f172a;
                color: #e2e8f0;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
        </style>
    </head>
    <body>
        <h3 style="color: #c4b5fd; margin-bottom: 8px">
            Controlar Producción · ${paisActual.value} · ${fecha.value}
        </h3>
        <table>
            ${encabezado}${filas}${totales}
        </table>
    </body>
</html>
`;
    const blob = new Blob([html], {
        type: 'application/vnd.ms-excel;charset=utf-8',
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `controlar_${pais.value}_${fecha.value}.xls`;
    a.click();
    URL.revokeObjectURL(url);
}

const COL_W = { serie: 110 } as const;
const serieLeft = 330;
</script>

<template>
    <Head title="Controlar Producción" />
    <Layout>
        <div class="min-h-screen bg-[#080c18] font-sans text-white">
            <div class="mx-auto max-w-[1700px] px-2 py-4 sm:px-4 sm:py-6">
                <header
                    class="mb-6 flex flex-col gap-3 sm:mb-8 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-semibold tracking-[0.2em] text-violet-500 uppercase"
                        >
                            Controlar — Producción
                        </p>
                        <h1
                            class="text-2xl font-black tracking-tight text-white sm:text-3xl"
                        >
                            Revisión
                            <span class="text-violet-400">por Máquina</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            {{ fecha }} · {{ datos.length }} máquinas
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <select
                            v-model="pais"
                            class="rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none"
                            @change="filtrar"
                        >
                            <option value="todos">Todos</option>
                            <option value="cl">Chile</option>
                            <option value="co">Colombia</option>
                            <option value="au">Australia</option>
                            <option value="pe">Perú</option>
                        </select>
                        <input
                            v-model="fecha"
                            type="date"
                            class="rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none"
                            @change="filtrar"
                        />
                        <button
                            @click="filtrar"
                            class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-500 active:scale-95"
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

                <div class="mb-4 grid grid-cols-2 gap-3 sm:mb-6 sm:grid-cols-4">
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-800/40 p-3 sm:p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-slate-500 uppercase sm:text-xs"
                        >
                            Máquinas
                        </p>
                        <p
                            class="mt-1 text-xl font-black text-white sm:text-2xl"
                        >
                            {{ datos.length }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-violet-900/50 bg-violet-900/20 p-3 sm:p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-violet-600 uppercase sm:text-xs"
                        >
                            Total
                        </p>
                        <p
                            class="mt-1 text-xl font-black text-violet-400 sm:text-2xl"
                        >
                            {{ fmt(totalMostrado) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-amber-900/50 bg-amber-900/20 p-3 sm:p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-amber-600 uppercase sm:text-xs"
                        >
                            Promedio/hora
                        </p>
                        <p
                            class="mt-1 text-xl font-black text-amber-400 sm:text-2xl"
                        >
                            {{ fmt(promedioMostrado) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-800/40 p-3 sm:p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-slate-500 uppercase sm:text-xs"
                        >
                            Registros
                        </p>
                        <p
                            class="mt-1 text-xl font-black text-white sm:text-2xl"
                        >
                            {{ fmtInt(totales?.conteo_general || 0) }}
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar centro, modelo o serie..."
                        class="w-full rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none sm:w-80 sm:py-3"
                    />
                </div>

                <div
                    class="overflow-x-auto rounded-2xl border border-slate-800/80 bg-slate-900/60 pb-2 shadow-2xl"
                >
                    <table class="w-full border-collapse text-sm">
                        <colgroup>
                            <col :style="{ width: '40px', minWidth: '40px' }" />
                            <col
                                :style="{ width: '130px', minWidth: '130px' }"
                            />
                            <col
                                :style="{ width: '160px', minWidth: '160px' }"
                            />
                            <col
                                :style="{
                                    width: COL_W.serie + 'px',
                                    minWidth: COL_W.serie + 'px',
                                }"
                            />
                        </colgroup>

                        <thead>
                            <tr class="border-b border-slate-800">
                                <th
                                    class="px-2 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    #
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Centro
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Modelo
                                </th>
                                <th
                                    class="z-20 border-r border-slate-800 bg-slate-900 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                    :style="{
                                        position: 'sticky',
                                        left: serieLeft + 'px',
                                    }"
                                >
                                    Serie
                                </th>
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Registros
                                </th>
                                <th
                                    class="min-w-[80px] border-r border-slate-800 bg-amber-950/40 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-amber-500 uppercase"
                                >
                                    Prom/h
                                </th>
                                <th
                                    class="min-w-[80px] bg-violet-950/40 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-violet-500 uppercase"
                                >
                                    Total
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
                                    class="px-2 py-2 text-center text-xs text-slate-600"
                                >
                                    {{ row.item }}
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-300">
                                    {{ row.NombreCentro }}
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-300">
                                    {{ row.Modelo }}
                                </td>
                                <td
                                    class="z-10 border-r border-slate-800/50 bg-[#080c18] px-3 py-2 text-center font-mono text-xs text-cyan-400"
                                    :style="{
                                        position: 'sticky',
                                        left: serieLeft + 'px',
                                    }"
                                >
                                    {{ row.CodigoMaquina }}
                                </td>
                                <td
                                    class="px-3 py-2 text-center text-xs text-slate-400"
                                >
                                    {{ fmtInt(row.conteo) }}
                                </td>
                                <td
                                    class="min-w-[80px] border-r border-slate-800 bg-amber-950/20 px-3 py-2 text-right text-xs font-bold tabular-nums"
                                    :class="colorPromedio(row.promedio)"
                                >
                                    {{ fmt(row.promedio) }}
                                </td>
                                <td
                                    class="min-w-[80px] bg-violet-950/20 px-3 py-2 text-right text-xs font-bold text-violet-400 tabular-nums"
                                >
                                    {{ fmt(row.total) }}
                                </td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr
                                class="border-t-2 border-slate-700 bg-slate-800/60"
                            >
                                <td
                                    colspan="3"
                                    class="px-3 py-3 text-right text-xs font-black tracking-widest text-white uppercase"
                                >
                                    Total
                                </td>
                                <td
                                    class="border-r border-slate-700 bg-slate-800 px-3 py-3 text-right text-xs font-black text-white uppercase"
                                    :style="{
                                        position: 'sticky',
                                        left: '40px',
                                    }"
                                ></td>
                                <td
                                    class="border-r border-slate-700/50 px-3 py-3 text-center text-xs font-semibold text-slate-300 tabular-nums"
                                >
                                    {{
                                        fmtInt(
                                            datosFiltrados.value.reduce(
                                                (s, r) =>
                                                    s +
                                                    (parseFloat(r.conteo) || 0),
                                                0,
                                            ),
                                        )
                                    }}
                                </td>
                                <td
                                    class="min-w-[80px] border-r border-slate-700 bg-amber-950/40 px-3 py-3 text-right text-sm font-black text-amber-300 tabular-nums"
                                >
                                    {{ fmt(promedioMostrado) }}
                                </td>
                                <td
                                    class="min-w-[80px] bg-violet-950/40 px-3 py-3 text-right text-sm font-black text-violet-300 tabular-nums"
                                >
                                    {{ fmt(totalMostrado) }}
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
