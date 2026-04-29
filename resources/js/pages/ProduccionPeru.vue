<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import Layout from '../components/Layout.vue';

const layoutRef = ref<InstanceType<typeof Layout>>();
const tableWrapper = ref<HTMLElement>();

const props = defineProps<{
    datos: any[];
    centros: any[];
    filtros: { fecha: string };
    horaInicio: number;
    horaFin: number;
    tipoCambio: number | null;
}>();

const fecha = ref(props.filtros.fecha);
const busqueda = ref('');
const scrollState = ref({ atStart: true, atEnd: false });

const horas = computed(() => {
    const arr: number[] = [];
    for (let h = props.horaInicio; h <= props.horaFin; h++) {
        arr.push(h);
    }
    return arr;
});

function onTableScroll() {
    const el = tableWrapper.value;
    if (!el) return;
    scrollState.value = {
        atStart: el.scrollLeft <= 4,
        atEnd: el.scrollLeft + el.clientWidth >= el.scrollWidth - 4,
    };
}

function scrollTable(dir: 'left' | 'right') {
    tableWrapper.value?.scrollBy({
        left: dir === 'right' ? 320 : -320,
        behavior: 'smooth',
    });
}

onMounted(() => {
    tableWrapper.value?.addEventListener('scroll', onTableScroll, { passive: true });
    onTableScroll();
});

onUnmounted(() => {
    tableWrapper.value?.removeEventListener('scroll', onTableScroll);
});

function filtrar() {
    router.get(
        '/produccion/peru',
        { fecha: fecha.value },
        { preserveScroll: true },
    );
}

function exportar() {
    const url = `/produccion/peru/pdf?fecha=${fecha.value}`;
    const printWindow = window.open(url, '_blank');
    printWindow?.addEventListener('load', () => {
        printWindow.document.title = `Producción Perú - ${fecha.value}`;
        printWindow.print();
        printWindow.matchMedia('print').addEventListener('change', (e) => {
            if (!e.matches) {
                printWindow.close();
            }
        });
    });
}

const fmt = (v: any) => {
    const n = parseFloat(v) || 0;
    return n.toLocaleString('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const datosFiltrados = computed(() => {
    const q = busqueda.value.toLowerCase();
    let datos = q
        ? props.datos.filter(
              (r) =>
                  r.centro?.toLowerCase().includes(q) ||
                  r.modelo?.toLowerCase().includes(q) ||
                  r.serie?.toLowerCase().includes(q),
          )
        : [...props.datos];
    return datos.sort((a, b) => (a.centro ?? '').localeCompare(b.centro ?? ''));
});

const totalGeneral = computed(() =>
    datosFiltrados.value.reduce((s, r) => s + (parseFloat(r.total) || 0), 0),
);

const promedioGeneral = computed(() => {
    if (!datosFiltrados.value.length) return 0;
    return (
        datosFiltrados.value.reduce(
            (s, r) => s + (parseFloat(r.promedio) || 0),
            0,
        ) / datosFiltrados.value.length
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

const promedioMin = computed(() => {
    const valores = datosFiltrados.value
        .map((r) => parseFloat(r.promedio) || 0)
        .filter((v) => v > 0);
    return valores.length ? Math.min(...valores) : 0;
});

const promedioMax = computed(() =>
    Math.max(
        ...datosFiltrados.value.map((r) => parseFloat(r.promedio) || 0),
        0,
    ),
);

function bgPromedio(colorCentro: number): string {
    if (colorCentro === 1) return 'background: rgba(134, 239, 122, 0.5)';
    if (colorCentro === 2) return 'background: rgba(252, 211, 6, 0.45)';
    if (colorCentro === 3) return 'background: rgba(252, 165, 165, 0.6)';
    return 'background: transparent';
}

function intensidad(valor: number, maxHora: number, transmitio: number): string {
    if (valor > 0) {
        if (maxHora === 0) return 'text-slate-600';
        const pct = valor / maxHora;
        if (pct >= 0.8) return 'text-green-500 font-semibold';
        if (pct >= 0.5) return 'text-green-600';
        if (pct >= 0.2) return 'text-green-700';
        return 'text-slate-600';
    }
    if (transmitio) return 'bg-blue-100 text-blue-400 font-semibold';
    return 'bg-red-100 text-red-500 font-semibold';
}

function claseTotal(row: any): string {
    const total = parseFloat(row.total) || 0;
    const promedio = parseFloat(row.promedio) || 0;
    if (total === 0) return 'bg-red-100 text-red-600 font-bold';
    if (promedio === 0 || total >= promedio) return 'bg-blue-100 text-blue-700 font-bold';
    return 'bg-red-100 text-red-600 font-bold';
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
    <Head title="Producción Perú" />
    <Layout ref="layoutRef">
        <div class="min-h-screen bg-slate-50 font-sans text-slate-700">
            <div class="w-full px-2 py-4 sm:px-4 sm:py-6">
                <header
                    class="mb-6 flex flex-col gap-3 sm:mb-8 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-semibold tracking-[0.2em] text-emerald-500 uppercase"
                        >
                            Producción — Perú
                        </p>
                        <h1
                            class="text-2xl font-black tracking-tight text-slate-700 sm:text-3xl"
                        >
                            Reporte
                            <span class="text-green-600">por Centro</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            {{ filtros.fecha }} · {{ datos.length }} máquinas
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            v-model="fecha"
                            type="date"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-green-500 focus:outline-none"
                        />
                        <button
                            @click="filtrar"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-emerald-500 active:scale-95"
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
                    <div class="rounded-xl border border-slate-200 bg-white/40 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-slate-500 uppercase sm:text-xs">
                            Máquinas
                        </p>
                        <p class="mt-1 text-xl font-black text-slate-700 sm:text-2xl">
                            {{ datos.length }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-green-200 bg-emerald-900/20 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-green-700 uppercase sm:text-xs">
                            Total
                        </p>
                        <p class="mt-1 text-xl font-black text-green-600 sm:text-2xl">
                            {{ fmt(totalGeneral) }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-900/20 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-amber-600 uppercase sm:text-xs">
                            Promedio/hora
                        </p>
                        <p class="mt-1 text-xl font-black text-amber-400 sm:text-2xl">
                            {{ fmt(promedioGeneral) }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white/40 p-3 sm:p-4">
                        <p class="text-[10px] tracking-wider text-slate-500 uppercase sm:text-xs">
                            Centros
                        </p>
                        <p class="mt-1 text-xl font-black text-slate-700 sm:text-2xl">
                            {{ centros.length }}
                        </p>
                    </div>
                </div>

                <div
                    class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                >
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar centro, modelo o serie..."
                        class="w-full rounded-xl border border-slate-700 bg-white/60 px-4 py-2.5 text-sm text-slate-700 placeholder-slate-600 focus:border-emerald-500 focus:outline-none sm:w-80 sm:py-3"
                    />
                    <div
                        class="flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs text-slate-500"
                    >
                        <span class="font-semibold whitespace-nowrap text-slate-600">Prom/h:</span>
                        <span class="flex items-center gap-1">
                            <span
                                class="inline-block h-3 w-8 rounded-sm"
                                style="background: rgba(252, 165, 165, 0.6)"
                            ></span>
                            <span class="whitespace-nowrap">Alto (máx)</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span
                                class="inline-block h-3 w-8 rounded-sm"
                                style="background: rgba(252, 211, 6, 0.45)"
                            ></span>
                            <span class="whitespace-nowrap">Medio</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span
                                class="inline-block h-3 w-8 rounded-sm"
                                style="background: rgba(134, 239, 122, 0.5)"
                            ></span>
                            <span class="whitespace-nowrap">Bajo (mín)</span>
                        </span>
                    </div>
                </div>

                <div class="relative">
                    <button
                        v-show="!scrollState.atStart"
                        @click="scrollTable('left')"
                        class="absolute left-2 top-1/2 z-30 -translate-y-1/2 flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white shadow-md text-lg text-slate-500 hover:text-slate-800 hover:border-slate-300 transition-all active:scale-95"
                        aria-label="Desplazar izquierda"
                    >
                        ‹
                    </button>

                    <button
                        v-show="!scrollState.atEnd"
                        @click="scrollTable('right')"
                        class="absolute right-2 top-1/2 z-30 -translate-y-1/2 flex h-9 w-9 items-center justify-center rounded-full border border-emerald-200 bg-white shadow-md text-lg text-emerald-400 hover:text-emerald-600 hover:border-emerald-300 transition-all active:scale-95 animate-pulse"
                        aria-label="Desplazar derecha"
                    >
                        ›
                    </button>

                    <div
                        v-show="!scrollState.atStart"
                        class="pointer-events-none absolute left-0 top-0 z-20 h-full w-12 rounded-l-2xl"
                        style="background: linear-gradient(to right, rgba(248,250,252,0.9), transparent)"
                    />

                    <div
                        v-show="!scrollState.atEnd"
                        class="pointer-events-none absolute right-0 top-0 z-20 h-full w-16 rounded-r-2xl"
                        style="background: linear-gradient(to right, transparent, rgba(248,250,252,0.9))"
                    />

                    <div
                        ref="tableWrapper"
                        class="w-full overflow-x-auto overflow-y-auto rounded-2xl border border-slate-200/80 bg-white/60 shadow-2xl scroll-smooth"
                        style="max-height: calc(100vh - 340px)"
                    >
                        <table
                            class="w-full border-collapse text-sm"
                            style="table-layout: auto"
                        >
                            <colgroup>
                                <col :style="{ width: COL_W.num + 'px', minWidth: COL_W.num + 'px' }" />
                                <col :style="{ width: COL_W.modelo + 'px', minWidth: COL_W.modelo + 'px' }" />
                                <col :style="{ width: COL_W.centro + 'px', minWidth: COL_W.centro + 'px' }" />
                                <col :style="{ width: COL_W.serie + 'px', minWidth: COL_W.serie + 'px' }" />
                            </colgroup>
                            <thead style="position: sticky; top: 0; z-index: 25">
                                <tr class="border-b border-slate-200 bg-white">
                                    <th
                                        class="bg-white px-2 py-3 text-center text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-500 uppercase"
                                        :style="{ position: 'sticky', left: stickyLeft.num + 'px', zIndex: 26 }"
                                    >
                                        #
                                    </th>
                                    <th
                                        class="bg-white px-3 py-3 text-left text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-500 uppercase"
                                        :style="{ position: 'sticky', left: stickyLeft.modelo + 'px', zIndex: 26 }"
                                    >
                                        Modelo
                                    </th>
                                    <th
                                        class="bg-white px-3 py-3 text-left text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-500 uppercase"
                                        :style="{ position: 'sticky', left: stickyLeft.centro + 'px', zIndex: 26 }"
                                    >
                                        Centro
                                    </th>
                                    <th
                                        class="border-r border-slate-200 bg-white px-3 py-3 text-center text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-500 uppercase"
                                        :style="{ position: 'sticky', left: stickyLeft.serie + 'px', zIndex: 26 }"
                                    >
                                        Serie
                                    </th>
                                    <th
                                        v-for="h in horas"
                                        :key="h"
                                        class="px-2 py-3 text-center text-[10px] font-bold tracking-widest whitespace-nowrap text-slate-500 uppercase"
                                    >
                                        {{ h }}h
                                    </th>
                                    <th class="bg-blue-50 px-3 py-3 text-center text-[10px] font-bold tracking-widest whitespace-nowrap text-blue-600 uppercase">
                                        Total
                                    </th>
                                    <th class="bg-amber-50 px-3 py-3 text-center text-[10px] font-bold tracking-widest whitespace-nowrap text-amber-600 uppercase">
                                        Prom/h
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="row in datosFiltrados"
                                    :key="row.item"
                                    class="border-b border-slate-200/40 hover:bg-white/30"
                                >
                                    <td
                                        class="z-10 bg-slate-50 px-2 py-2 text-center text-xs whitespace-nowrap text-slate-600"
                                        :style="{ position: 'sticky', left: stickyLeft.num + 'px' }"
                                    >
                                        {{ row.item }}
                                    </td>
                                    <td
                                        class="z-10 bg-slate-50 px-3 py-2 text-xs whitespace-nowrap text-slate-300"
                                        :style="{ position: 'sticky', left: stickyLeft.modelo + 'px' }"
                                    >
                                        {{ row.modelo }}
                                    </td>
                                    <td
                                        class="z-10 bg-slate-50 px-3 py-2 text-xs whitespace-nowrap text-slate-300"
                                        :style="{ position: 'sticky', left: stickyLeft.centro + 'px' }"
                                    >
                                        {{ row.centro }}
                                    </td>
                                    <td
                                        class="z-10 border-r border-slate-200/50 bg-slate-50 px-3 py-2 text-center font-mono text-xs whitespace-nowrap text-cyan-400"
                                        :style="{ position: 'sticky', left: stickyLeft.serie + 'px' }"
                                    >
                                        {{ row.serie }}
                                    </td>
                                    <td
                                        v-for="h in horas"
                                        :key="h"
                                        class="px-2 py-2 text-center text-xs whitespace-nowrap tabular-nums transition-colors"
                                        :class="
                                            intensidad(
                                                parseFloat(row[`h${h}`]) || 0,
                                                maxPorHora[h],
                                                row[`trans_h${h}`] ?? 0,
                                            )
                                        "
                                    >
                                        {{ fmt(row[`h${h}`]) }}
                                    </td>
                                    <td
                                        class="rounded-sm px-3 py-2 text-right text-xs whitespace-nowrap tabular-nums"
                                        :class="claseTotal(row)"
                                    >
                                        {{ fmt(row.total) }}
                                    </td>
                                    <td
                                        class="rounded-sm px-3 py-2 text-right text-xs font-semibold whitespace-nowrap text-slate-700 tabular-nums transition-colors"
                                        :style="bgPromedio(row.colorCentro)"
                                    >
                                        {{ fmt(row.promedio) }}
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot style="position: sticky; bottom: 0; z-index: 25">
                                <tr class="border-t-2 border-slate-700 bg-white/60">
                                    <td
                                        colspan="4"
                                        class="border-r border-slate-700 bg-white px-3 py-3 text-right text-xs font-black tracking-widest whitespace-nowrap text-slate-700 uppercase"
                                        :style="{ position: 'sticky', left: stickyLeft.num + 'px', zIndex: 26 }"
                                    >
                                        Total
                                    </td>
                                    <td
                                        v-for="h in horas"
                                        :key="h"
                                        class="border-r border-slate-700/50 px-2 py-3 text-center text-xs font-semibold whitespace-nowrap text-green-600 tabular-nums"
                                    >
                                        {{ fmt(totalesHora[h]) }}
                                    </td>
                                    <td class="bg-blue-50 px-3 py-3 text-right text-sm font-black whitespace-nowrap text-blue-600 tabular-nums">
                                        {{ fmt(totalGeneral) }}
                                    </td>
                                    <td class="bg-amber-950/40 px-3 py-3 text-right text-sm font-black whitespace-nowrap text-amber-300 tabular-nums">
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
        </div>
    </Layout>
</template>