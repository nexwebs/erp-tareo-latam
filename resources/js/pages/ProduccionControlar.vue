<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import Layout from '../components/Layout.vue';

const props = defineProps<{
    datos: any[];
    centros: any[];
    totales: {
        total_general: number;
        conteo_general: number;
        horas_activas_total: number;
        horas_muertas_total: number;
        eficiencia_global: number;
    };
    filtros: { fecha: string; fecha_fin: string; pais: string };
    horaInicio: number;
    horaFin: number;
}>();

const paisNombre: Record<string, string> = {
    chile: 'Chile',
    colombia: 'Colombia',
    australia: 'Australia',
    peru: 'Perú',
};

const pais = ref(props.filtros.pais);
const fecha = ref(props.filtros.fecha);
const fechaFin = ref(props.filtros.fecha_fin ?? props.filtros.fecha);
const busqueda = ref('');

const paisActual = computed(() => paisNombre[pais.value] ?? pais.value);

function filtrar() {
    router.get(
        '/produccion/controlar',
        { fecha: fecha.value, fecha_fin: fechaFin.value, pais: pais.value },
        { preserveScroll: true },
    );
}

const fmt = (v: any) => {
    const n = parseFloat(v) || 0;
    return n.toLocaleString('es-PE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
};
const fmtInt = (v: any) =>
    Math.round(parseFloat(v) || 0).toLocaleString('es-PE');
const fmtPct = (v: any) => `${parseFloat(v) || 0}%`;

const datos = computed(() => props.datos ?? []);

const datosFiltrados = computed(() => {
    const q = busqueda.value.toLowerCase();
    if (!q) return datos.value;
    return datos.value.filter(
        (r) =>
            r.NombreCentro?.toLowerCase().includes(q) ||
            r.Modelo?.toLowerCase().includes(q) ||
            r.Serie?.toLowerCase().includes(q),
    );
});

const totalMostrado = computed(() =>
    datosFiltrados.value.reduce(
        (s: number, r: any) => s + (parseFloat(r.total) || 0),
        0,
    ),
);
const promedioMostrado = computed(() => {
    const arr = datosFiltrados.value;
    if (!arr.length) return 0;
    return (
        arr.reduce(
            (s: number, r: any) =>
                s + (parseFloat(r.total_promedio_diario) || 0),
            0,
        ) / arr.length
    );
});
const conteoTotal = computed(() =>
    datosFiltrados.value.reduce(
        (s: number, r: any) => s + (parseInt(r.dias_con_datos) || 0),
        0,
    ),
);
const horasActivasMostradas = computed(() =>
    datosFiltrados.value.reduce(
        (s: number, r: any) => s + (parseInt(r.horas_con_produccion) || 0),
        0,
    ),
);
const horasMuertasMostradas = computed(() =>
    datosFiltrados.value.reduce(
        (s: number, r: any) => s + (parseInt(r.horas_sin_transmitir) || 0),
        0,
    ),
);
const eficienciaMedia = computed(() => {
    const total = horasActivasMostradas.value + horasMuertasMostradas.value;
    return total > 0
        ? ((horasActivasMostradas.value / total) * 100).toFixed(1)
        : '0';
});

const statsHistorico = computed(() => {
    const values = datos.value
        .map((r: any) => parseFloat(r.promedioCentro) || 0)
        .filter((v) => v > 0);
    if (!values.length) return { media: 0 };
    return { media: values.reduce((a, b) => a + b, 0) / values.length };
});

function colorEficiencia(ef: number): string {
    if (ef >= 70) return 'text-emerald-500';
    if (ef >= 50) return 'text-amber-500';
    return 'text-red-500';
}

function colorVsHistorico(vs: number | null): string {
    if (vs === null) return 'text-slate-400';
    if (vs >= 0) return 'text-emerald-500';
    if (vs >= -20) return 'text-amber-500';
    return 'text-red-500';
}

function colorPromedio(promDiario: number): string {
    const { media } = statsHistorico.value;
    if (!media) return 'text-slate-400';
    if (promDiario >= media) return 'text-emerald-500';
    if (promDiario >= media * 0.7) return 'text-amber-500';
    return 'text-red-500';
}

function exportar() {
    window.open(
        `/produccion/controlar/pdf?fecha=${fecha.value}&pais=${pais.value}`,
        '_blank',
    );
}
</script>

<template>
    <Head title="Controlar Producción" />
    <Layout>
        <div class="min-h-screen bg-slate-50 font-sans text-slate-700">
            <div class="mx-auto max-w-[1900px] px-2 py-4 sm:px-4 sm:py-6">
                <header
                    class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-semibold tracking-[0.2em] text-violet-500 uppercase"
                        >
                            Controlar — Producción
                        </p>
                        <h1
                            class="text-2xl font-black tracking-tight text-slate-800 sm:text-3xl"
                        >
                            Revisión
                            <span class="text-violet-400">por Máquina</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ fecha
                            }}{{ fecha !== fechaFin ? ' → ' + fechaFin : '' }} ·
                            {{ paisActual }} · {{ datos.length }} máquinas
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <select
                            v-model="pais"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-violet-500 focus:outline-none"
                            @change="filtrar"
                        >
                            <option value="peru">Perú</option>
                            <option value="chile">Chile</option>
                            <option value="colombia">Colombia</option>
                            <option value="australia">Australia</option>
                        </select>
                        <input
                            v-model="fecha"
                            type="date"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-violet-500 focus:outline-none"
                        />
                        <input
                            v-model="fechaFin"
                            type="date"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-violet-500 focus:outline-none"
                        />
                        <button
                            @click="filtrar"
                            class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-500 active:scale-95"
                        >
                            Filtrar
                        </button>
                        <button
                            @click="exportar"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-400 active:scale-95"
                        >
                            ↓ PDF
                        </button>
                    </div>
                </header>

                <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-slate-400 uppercase"
                        >
                            Máquinas
                        </p>
                        <p class="mt-1 text-2xl font-black text-slate-700">
                            {{ datos.length }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-violet-200 bg-violet-50 p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-violet-500 uppercase"
                        >
                            Total
                        </p>
                        <p class="mt-1 text-2xl font-black text-violet-700">
                            {{ fmt(totalMostrado) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-amber-200 bg-amber-50 p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-amber-600 uppercase"
                        >
                            Prom/día
                        </p>
                        <p class="mt-1 text-2xl font-black text-amber-700">
                            {{ fmt(promedioMostrado) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-emerald-200 bg-emerald-50 p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-emerald-600 uppercase"
                        >
                            H. activas
                        </p>
                        <p class="mt-1 text-2xl font-black text-emerald-700">
                            {{ fmtInt(horasActivasMostradas) }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <p
                            class="text-[10px] tracking-wider text-red-500 uppercase"
                        >
                            H. muertas
                        </p>
                        <p class="mt-1 text-2xl font-black text-red-600">
                            {{ fmtInt(horasMuertasMostradas) }}
                        </p>
                        <p class="text-xs text-red-400">
                            Eficiencia: {{ eficienciaMedia }}%
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar centro, modelo o serie..."
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm placeholder-slate-400 focus:border-violet-500 focus:outline-none sm:w-96"
                    />
                </div>

                <div
                    class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th
                                    class="px-2 py-3 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    #
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    Centro
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    Modelo
                                </th>
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    Serie
                                </th>
                                <th
                                    class="px-3 py-3 text-right text-[10px] font-bold tracking-widest text-violet-500 uppercase"
                                >
                                    Total
                                </th>
                                <th
                                    class="px-3 py-3 text-right text-[10px] font-bold tracking-widest text-amber-500 uppercase"
                                >
                                    Prom/día
                                </th>
                                <th
                                    class="px-3 py-3 text-right text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    Prom hist.
                                </th>
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    vs Hist.
                                </th>
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-emerald-600 uppercase"
                                >
                                    H. activas
                                </th>
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-red-500 uppercase"
                                >
                                    H. muertas
                                </th>
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase"
                                >
                                    Eficiencia
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
                                    class="px-2 py-2 text-center text-xs text-slate-400"
                                >
                                    {{ row.item }}
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-600">
                                    {{ row.NombreCentro }}
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-500">
                                    {{ row.Modelo }}
                                </td>
                                <td
                                    class="px-3 py-2 text-center font-mono text-xs text-cyan-600"
                                >
                                    {{ row.Serie }}
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-xs font-bold text-violet-600 tabular-nums"
                                >
                                    {{ fmt(row.total) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-xs font-bold tabular-nums"
                                    :class="
                                        colorPromedio(row.total_promedio_diario)
                                    "
                                >
                                    {{ fmt(row.total_promedio_diario) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-xs text-slate-400 tabular-nums"
                                >
                                    {{ fmt(row.promedioCentro) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-center text-xs font-bold tabular-nums"
                                    :class="colorVsHistorico(row.vs_historico)"
                                >
                                    <span v-if="row.vs_historico !== null">
                                        {{ row.vs_historico > 0 ? '+' : ''
                                        }}{{ row.vs_historico }}%
                                    </span>
                                    <span v-else class="text-slate-300">—</span>
                                </td>
                                <td
                                    class="px-3 py-2 text-center text-xs font-semibold text-emerald-600"
                                >
                                    {{ row.horas_con_produccion }}h
                                </td>
                                <td
                                    class="px-3 py-2 text-center text-xs font-semibold text-red-500"
                                >
                                    {{ row.horas_sin_transmitir }}h
                                </td>
                                <td
                                    class="px-3 py-2 text-center text-xs font-bold"
                                    :class="colorEficiencia(row.eficiencia)"
                                >
                                    {{ row.eficiencia }}%
                                </td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="border-t-2 border-slate-200 bg-slate-50">
                                <td
                                    colspan="4"
                                    class="px-3 py-3 text-right text-xs font-black tracking-widest text-slate-500 uppercase"
                                >
                                    Total filtrado
                                </td>
                                <td
                                    class="px-3 py-3 text-right text-sm font-black text-violet-600 tabular-nums"
                                >
                                    {{ fmt(totalMostrado) }}
                                </td>
                                <td
                                    class="px-3 py-3 text-right text-sm font-black text-amber-600 tabular-nums"
                                >
                                    {{ fmt(promedioMostrado) }}
                                </td>
                                <td class="px-3 py-3"></td>
                                <td class="px-3 py-3"></td>
                                <td
                                    class="px-3 py-3 text-center text-sm font-black text-emerald-600"
                                >
                                    {{ fmtInt(horasActivasMostradas) }}h
                                </td>
                                <td
                                    class="px-3 py-3 text-center text-sm font-black text-red-500"
                                >
                                    {{ fmtInt(horasMuertasMostradas) }}h
                                </td>
                                <td
                                    class="px-3 py-3 text-center text-sm font-black text-slate-600"
                                >
                                    {{ eficienciaMedia }}%
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <p
                        v-if="!datosFiltrados.length"
                        class="py-16 text-center text-slate-400"
                    >
                        Sin registros para los filtros aplicados.
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>
