<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    datos: any[];
    centros: any[];
    totales: { total_general: number; conteo_general: number } | null;
    filtros: { fecha: string; pais: string };
    horaInicio: number;
    horaFin: number;
}>();

const fecha    = ref(props.filtros.fecha);
const pais     = ref(props.filtros.pais);
const busqueda = ref('');

const paisNombre: Record<string, string> = {
    peru: 'Perú', chile: 'Chile', colombia: 'Colombia', australia: 'Australia',
};

function filtrar() {
    router.get('/produccion/controlar', { fecha: fecha.value, pais: pais.value }, { preserveScroll: true });
}

const fmt = (v: number) =>
    new Intl.NumberFormat('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v || 0);

const fmtInt = (v: number) =>
    new Intl.NumberFormat('es-PE').format(v || 0);

const datosFiltrados = computed(() => {
    const q = busqueda.value.toLowerCase();
    if (!q) return props.datos;
    return props.datos.filter(
        r =>
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
    const suma = datosFiltrados.value.reduce((s, r) => s + (parseFloat(r.promedio) || 0), 0);
    return suma / datosFiltrados.value.length;
});

const maxTotal = computed(() =>
    Math.max(...props.datos.map(r => parseFloat(r.total) || 0), 1),
);

function barWidth(total: number): string {
    return `${Math.round((total / maxTotal.value) * 100)}%`;
}
</script>

<template>
    <div class="min-h-screen bg-[#080c18] font-sans text-white">
        <div class="mx-auto max-w-[1400px] px-4 py-6">

            <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.2em] text-violet-400">
                        Control de producción — {{ paisNombre[pais] }}
                    </p>
                    <h1 class="text-3xl font-black tracking-tight text-white">
                        Controlar <span class="text-violet-400">{{ filtros.fecha }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ datos.length }} máquinas registradas hoy
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input
                        v-model="fecha"
                        type="date"
                        class="rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none"
                    />
                    <select
                        v-model="pais"
                        class="rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none"
                    >
                        <option value="peru">Perú</option>
                        <option value="chile">Chile</option>
                        <option value="colombia">Colombia</option>
                        <option value="australia">Australia</option>
                    </select>
                    <button
                        @click="filtrar"
                        class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-500 active:scale-95"
                    >
                        Filtrar
                    </button>
                </div>
            </header>

            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl border border-slate-800 bg-slate-800/40 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-500">Máquinas</p>
                    <p class="mt-1 text-2xl font-black text-white">{{ datos.length }}</p>
                </div>
                <div class="rounded-xl border border-emerald-900/50 bg-emerald-900/20 p-4">
                    <p class="text-xs uppercase tracking-wider text-emerald-600">Total general</p>
                    <p class="mt-1 text-2xl font-black text-emerald-400">{{ fmt(totales?.total_general ?? 0) }}</p>
                </div>
                <div class="rounded-xl border border-violet-900/50 bg-violet-900/20 p-4">
                    <p class="text-xs uppercase tracking-wider text-violet-500">Registros</p>
                    <p class="mt-1 text-2xl font-black text-violet-400">{{ fmtInt(totales?.conteo_general ?? 0) }}</p>
                </div>
                <div class="rounded-xl border border-amber-900/50 bg-amber-900/20 p-4">
                    <p class="text-xs uppercase tracking-wider text-amber-600">Prom. por máq.</p>
                    <p class="mt-1 text-2xl font-black text-amber-400">{{ fmt(promedioMostrado) }}</p>
                </div>
            </div>

            <div class="mb-4">
                <input
                    v-model="busqueda"
                    type="search"
                    placeholder="Buscar centro, modelo o serie..."
                    class="w-full rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-3 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none sm:w-80"
                />
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-800/80 bg-slate-900/60 shadow-2xl">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-800">
                            <th class="px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">#</th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">Centro</th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">Modelo</th>
                            <th class="px-3 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-500">Serie</th>
                            <th class="px-3 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-500">Registros</th>
                            <th class="px-3 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-amber-600">Promedio/h</th>
                            <th class="px-3 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-emerald-600">Total</th>
                            <th class="px-3 py-3 text-[10px] font-bold uppercase tracking-widest text-slate-600">Distribución</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in datosFiltrados"
                            :key="row.item"
                            class="border-b border-slate-800/40 transition-colors hover:bg-slate-800/30"
                        >
                            <td class="px-3 py-2 text-xs text-slate-600">{{ row.item }}</td>
                            <td class="max-w-[180px] truncate px-3 py-2 text-xs text-slate-300">{{ row.NombreCentro }}</td>
                            <td class="px-3 py-2 text-xs text-slate-300">{{ row.Modelo }}</td>
                            <td class="px-3 py-2 text-center font-mono text-xs text-cyan-400">{{ row.CodigoMaquina }}</td>
                            <td class="px-3 py-2 text-center text-xs text-slate-400">{{ fmtInt(row.conteo) }}</td>
                            <td class="px-3 py-2 text-right text-xs font-semibold tabular-nums text-amber-400">{{ fmt(row.promedio) }}</td>
                            <td class="px-3 py-2 text-right text-xs font-bold tabular-nums text-emerald-400">{{ fmt(row.total) }}</td>
                            <td class="px-3 py-2">
                                <div class="h-1.5 w-full min-w-[80px] overflow-hidden rounded-full bg-slate-800">
                                    <div
                                        class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                                        :style="{ width: barWidth(parseFloat(row.total) || 0) }"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-700 bg-slate-800/60">
                            <td colspan="5" class="px-3 py-3 text-right text-xs font-black uppercase tracking-widest text-white">Mostrado</td>
                            <td class="px-3 py-3 text-right text-xs font-black tabular-nums text-amber-300">{{ fmt(promedioMostrado) }}</td>
                            <td class="px-3 py-3 text-right text-sm font-black tabular-nums text-emerald-300">{{ fmt(totalMostrado) }}</td>
                            <td />
                        </tr>
                    </tfoot>
                </table>
                <p v-if="!datosFiltrados.length" class="py-16 text-center text-slate-600">Sin registros para los filtros aplicados.</p>
            </div>
        </div>
    </div>
</template>