<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Layout from '../components/Layout.vue';

const props = defineProps<{
    datos: any[];
    centros: any[];
    totales: {
        total_general: number;
        conteo_general: number;
        horas_activas_total: number;
        horas_cero_total: number;
        horas_muertas_total: number;
        disponibilidad_global: number;
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

const pais          = ref(props.filtros.pais);
const fecha         = ref(props.filtros.fecha);
const fechaFin      = ref(props.filtros.fecha_fin ?? props.filtros.fecha);
const busqueda      = ref('');
const metricaActiva = ref<'disponibilidad' | 'rendimiento' | 'eficiencia' | null>(null);

const paisActual = computed(() => paisNombre[pais.value] ?? pais.value);

function filtrar() {
    router.get(
        '/produccion/eficiencia',
        { fecha: fecha.value, fecha_fin: fechaFin.value, pais: pais.value },
        { preserveScroll: true },
    );
}

function toggleMetrica(key: 'disponibilidad' | 'rendimiento' | 'eficiencia') {
    metricaActiva.value = metricaActiva.value === key ? null : key;
}

const fmt    = (v: any) => (parseFloat(v) || 0).toLocaleString('es-PE', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
const fmtInt = (v: any) => Math.round(parseFloat(v) || 0).toLocaleString('es-PE');

const datos          = computed(() => props.datos ?? []);
const datosFiltrados = computed(() => {
    const q = busqueda.value.toLowerCase();

    if (!q) {
return datos.value;
}

    return datos.value.filter(
        (r) =>
            r.NombreCentro?.toLowerCase().includes(q) ||
            r.Modelo?.toLowerCase().includes(q) ||
            r.Serie?.toLowerCase().includes(q),
    );
});

const totalMostrado         = computed(() => datosFiltrados.value.reduce((s: number, r: any) => s + (parseFloat(r.total) || 0), 0));
const promedioMostrado      = computed(() => {
    const arr = datosFiltrados.value;

    if (!arr.length) {
return 0;
}

    return arr.reduce((s: number, r: any) => s + (parseFloat(r.total_promedio_diario) || 0), 0) / arr.length;
});
const horasActivasMostradas = computed(() => datosFiltrados.value.reduce((s: number, r: any) => s + (parseInt(r.horas_con_produccion) || 0), 0));
const horasCeroMostradas    = computed(() => datosFiltrados.value.reduce((s: number, r: any) => s + (parseInt(r.horas_con_produccion_cero) || 0), 0));
const horasMuertasMostradas = computed(() => datosFiltrados.value.reduce((s: number, r: any) => s + (parseInt(r.horas_sin_transmitir) || 0), 0));

const disponibilidadMedia = computed(() => {
    const maquinas   = datosFiltrados.value.length;
    const horasTurno = maquinas * 16;

    return horasTurno > 0
        ? (((horasActivasMostradas.value + horasCeroMostradas.value) / horasTurno) * 100).toFixed(1)
        : '0';
});

const rendimientoMedio = computed(() => {
    const arr = datosFiltrados.value;

    if (!arr.length) {
return '0';
}

    return (arr.reduce((s: number, r: any) => s + (parseFloat(r.rendimiento) || 0), 0) / arr.length).toFixed(1);
});

const eficienciaRealMedia = computed(() => {
    const arr = datosFiltrados.value;

    if (!arr.length) {
return '0';
}

    return (arr.reduce((s: number, r: any) => s + (parseFloat(r.eficiencia_real) || 0), 0) / arr.length).toFixed(1);
});

function colorEficiencia(ef: number): string {
    if (ef >= 70) {
return 'text-emerald-500';
}

    if (ef >= 40) {
return 'text-amber-500';
}

    return 'text-red-500';
}

function colorVsHistorico(vs: number | null): string {
    if (vs === null) {
return 'text-slate-400';
}

    if (vs >= 0)    {
return 'text-emerald-500';
}

    if (vs >= -20)  {
return 'text-amber-500';
}

    return 'text-red-500';
}

function exportar() {
    const url = `/produccion/controlar/pdf?fecha=${fecha.value}&pais=${pais.value}`;
    const printWindow = window.open(url, '_blank');
    printWindow?.addEventListener('load', () => {
        printWindow.print();
        printWindow.matchMedia('print').addEventListener('change', (e) => {
            if (!e.matches) {
printWindow.close();
}
        });
    });
}

const metricas = computed(() => ({
    disponibilidad: {
        titulo: 'Disponibilidad',
        valor: `${disponibilidadMedia.value}%`,
        formula: '(H. Activas + H. Cero) / 16 × 100',
        descripcion: 'Mide qué fracción del turno de 16 horas (8h–23h) la máquina estuvo presente en el sistema, sin importar si produjo o no. Una hora con registro aunque sea de producción = 0 cuenta como disponible.',
        casos: [
            {
                titulo: 'Conectada todo el turno',
                filas: ['H. Activas: 13', 'H. Cero: 3', 'H. Sin Trans.: 0'],
                resultado: '(13 + 3) / 16 = 100%',
                badge: 'good',
                label: 'Conectada',
            },
            {
                titulo: 'Parcialmente disponible',
                filas: ['H. Activas: 6', 'H. Cero: 2', 'H. Sin Trans.: 8'],
                resultado: '(6 + 2) / 16 = 50%',
                badge: 'warn',
                label: 'Alerta',
            },
            {
                titulo: 'Sin transmisión',
                filas: ['H. Activas: 0', 'H. Cero: 0', 'H. Sin Trans.: 16'],
                resultado: '(0 + 0) / 16 = 0%',
                badge: 'bad',
                label: 'Sin señal',
            },
        ],
    },
    rendimiento: {
        titulo: 'Rendimiento',
        valor: `${rendimientoMedio.value}%`,
        formula: 'MIN(prom_diario / prom_historico, 1.0) × 100',
        descripcion: 'Compara la producción promedio del período contra el histórico de las últimas 4 semanas del mismo día de semana. Se limita a 100% — sobreproducir no incrementa este índice. Si no hay histórico y hay producción, se asume 100%.',
        casos: [
            {
                titulo: 'Supera el histórico',
                filas: ['Prom diario: 496', 'Prom hist.: 292', 'Ratio: 496/292 = 1.70'],
                resultado: 'MIN(1.70, 1.0) = 100%',
                badge: 'good',
                label: 'Sobre histórico',
            },
            {
                titulo: 'Por debajo del histórico',
                filas: ['Prom diario: 85', 'Prom hist.: 170', 'Ratio: 85/170 = 0.50'],
                resultado: 'MIN(0.50, 1.0) = 50%',
                badge: 'warn',
                label: 'Bajo histórico',
            },
            {
                titulo: 'Sin producción',
                filas: ['Prom diario: 0', 'Prom hist.: 58', 'Ratio: 0/58 = 0'],
                resultado: 'MIN(0, 1.0) = 0%',
                badge: 'bad',
                label: 'Sin producción',
            },
        ],
    },
    eficiencia: {
        titulo: 'Eficiencia Real',
        valor: `${eficienciaRealMedia.value}%`,
        formula: 'Disponibilidad × Rendimiento / 100',
        descripcion: 'Indicador compuesto que combina disponibilidad (¿estuvo conectada?) y rendimiento (¿produjo respecto a su histórico?). Si cualquiera de los dos es 0, la eficiencia real es 0. Ambos factores deben ser altos para un buen resultado.',
        casos: [
            {
                titulo: 'Conectada y produce bien',
                filas: ['Disponibilidad: 100%', 'Rendimiento: 90%'],
                resultado: '100% × 90% / 100 = 90%',
                badge: 'good',
                label: 'Óptimo',
            },
            {
                titulo: 'Conectada pero produce poco',
                filas: ['Disponibilidad: 100%', 'Rendimiento: 20%'],
                resultado: '100% × 20% / 100 = 20%',
                badge: 'warn',
                label: 'Alerta',
            },
            {
                titulo: 'Sin transmisión',
                filas: ['Disponibilidad: 0%', 'Rendimiento: 0%'],
                resultado: '0% × 0% / 100 = 0%',
                badge: 'bad',
                label: 'Crítico',
            },
        ],
    },
}));
</script>

<template>
    <Head title="Controlar Producción" />
    <Layout>
        <div class="min-h-screen bg-slate-50 font-sans text-slate-700">
            <div class="mx-auto max-w-[1900px] px-2 py-4 sm:px-4 sm:py-6">

                <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-1 text-xs font-semibold tracking-[0.2em] text-violet-500 uppercase">Controlar — Producción</p>
                        <h1 class="text-2xl font-black tracking-tight text-slate-800 sm:text-3xl">
                            Revisión <span class="text-violet-400">por Máquina</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ fecha }}{{ fecha !== fechaFin ? ' → ' + fechaFin : '' }} · {{ paisActual }} · {{ datos.length }} máquinas
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <select v-model="pais" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none" @change="filtrar">
                            <option value="peru">Perú</option>
                            <option value="chile">Chile</option>
                            <option value="colombia">Colombia</option>
                            <option value="australia">Australia</option>
                        </select>
                        <input v-model="fecha"    type="date" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none" />
                        <input v-model="fechaFin" type="date" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none" />
                        <button @click="filtrar"  class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-bold text-white hover:bg-violet-500 active:scale-95">Filtrar</button>
                        <button @click="exportar" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-400 active:scale-95">↓ PDF</button>
                    </div>
                </header>

                <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-6">
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <p class="text-[10px] tracking-wider text-slate-400 uppercase">Máquinas</p>
                        <p class="mt-1 text-2xl font-black text-slate-700">{{ datos.length }}</p>
                    </div>
                    <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
                        <p class="text-[10px] tracking-wider text-violet-500 uppercase">Total</p>
                        <p class="mt-1 text-2xl font-black text-violet-700">{{ fmt(totalMostrado) }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-[10px] tracking-wider text-amber-600 uppercase">Prom/día</p>
                        <p class="mt-1 text-2xl font-black text-amber-700">{{ fmt(promedioMostrado) }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-[10px] tracking-wider text-emerald-600 uppercase">H. Activas</p>
                        <p class="mt-1 text-2xl font-black text-emerald-700">{{ fmtInt(horasActivasMostradas) }}</p>
                    </div>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                        <p class="text-[10px] tracking-wider text-blue-500 uppercase">H. Cero</p>
                        <p class="mt-1 text-2xl font-black text-blue-600">{{ fmtInt(horasCeroMostradas) }}</p>
                    </div>
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <p class="text-[10px] tracking-wider text-red-500 uppercase">H. Sin Trans.</p>
                        <p class="mt-1 text-2xl font-black text-red-600">{{ fmtInt(horasMuertasMostradas) }}</p>
                    </div>
                </div>

                <div class="mb-4">
                    <input v-model="busqueda" type="search" placeholder="Buscar centro, modelo o serie..."
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm placeholder-slate-400 focus:border-violet-500 focus:outline-none sm:w-96" />
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-2 py-3 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase">#</th>
                                <th class="px-3 py-3 text-left   text-[10px] font-bold tracking-widest text-slate-400 uppercase">Centro</th>
                                <th class="px-3 py-3 text-left   text-[10px] font-bold tracking-widest text-slate-400 uppercase">Modelo</th>
                                <th class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase">Serie</th>
                                <th class="px-3 py-3 text-right  text-[10px] font-bold tracking-widest text-violet-500 uppercase">Total</th>
                                <th class="px-3 py-3 text-right  text-[10px] font-bold tracking-widest text-amber-500 uppercase">Prom/día</th>
                                <th class="px-3 py-3 text-right  text-[10px] font-bold tracking-widest text-slate-400 uppercase">Prom hist.</th>
                                <th class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase">vs Hist.</th>
                                <th class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-emerald-600 uppercase">H. Activas</th>
                                <th class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-blue-500 uppercase">H. Cero</th>
                                <th class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-red-500 uppercase">H. Sin Trans.</th>
                                <th
                                    class="cursor-pointer select-none px-3 py-3 text-center text-[10px] font-bold tracking-widest uppercase transition-colors"
                                    :class="metricaActiva === 'disponibilidad' ? 'bg-violet-50 text-violet-600' : 'text-emerald-600 hover:bg-slate-100 hover:text-violet-500'"
                                    @click="toggleMetrica('disponibilidad')"
                                >
                                    Disp. ↗
                                </th>
                                <th
                                    class="cursor-pointer select-none px-3 py-3 text-center text-[10px] font-bold tracking-widest uppercase transition-colors"
                                    :class="metricaActiva === 'rendimiento' ? 'bg-violet-50 text-violet-600' : 'text-violet-500 hover:bg-slate-100 hover:text-violet-700'"
                                    @click="toggleMetrica('rendimiento')"
                                >
                                    Rend. ↗
                                </th>
                                <th
                                    class="cursor-pointer select-none px-3 py-3 text-center text-[10px] font-bold tracking-widest uppercase transition-colors"
                                    :class="metricaActiva === 'eficiencia' ? 'bg-violet-50 text-violet-600' : 'text-indigo-600 hover:bg-slate-100 hover:text-violet-700'"
                                    @click="toggleMetrica('eficiencia')"
                                >
                                    Efic. Real ↗
                                </th>
                            </tr>

                            <tr v-if="metricaActiva">
                                <td colspan="14" class="p-0">
                                    <div class="border-b border-violet-200 bg-violet-50 px-5 py-4">
                                        <div class="mb-3 flex items-start justify-between">
                                            <div>
                                                <p class="text-[10px] font-semibold tracking-widest text-violet-400 uppercase">Cómo se calcula</p>
                                                <p class="mt-0.5 text-sm font-black text-slate-800">{{ metricas[metricaActiva].titulo }}</p>
                                            </div>
                                            <button @click="metricaActiva = null" class="text-slate-400 hover:text-slate-600">✕</button>
                                        </div>

                                        <div class="mb-3 rounded-lg border border-violet-100 bg-white px-4 py-2 font-mono text-xs text-slate-700">
                                            {{ metricas[metricaActiva].formula }}
                                        </div>

                                        <p class="mb-3 text-xs leading-relaxed text-slate-600">{{ metricas[metricaActiva].descripcion }}</p>

                                        <div class="grid grid-cols-3 gap-3">
                                            <div
                                                v-for="caso in metricas[metricaActiva].casos"
                                                :key="caso.titulo"
                                                class="rounded-xl border p-3"
                                                :class="{
                                                    'border-emerald-200 bg-emerald-50': caso.badge === 'good',
                                                    'border-amber-200  bg-amber-50':   caso.badge === 'warn',
                                                    'border-red-200    bg-red-50':     caso.badge === 'bad',
                                                }"
                                            >
                                                <p class="mb-1.5 text-xs font-semibold"
                                                    :class="{
                                                        'text-emerald-700': caso.badge === 'good',
                                                        'text-amber-700':   caso.badge === 'warn',
                                                        'text-red-700':     caso.badge === 'bad',
                                                    }"
                                                >{{ caso.titulo }}</p>
                                                <p v-for="f in caso.filas" :key="f" class="font-mono text-[11px] leading-relaxed text-slate-500">{{ f }}</p>
                                                <div
                                                    class="mt-2 border-t pt-2"
                                                    :class="{
                                                        'border-emerald-200': caso.badge === 'good',
                                                        'border-amber-200':   caso.badge === 'warn',
                                                        'border-red-200':     caso.badge === 'bad',
                                                    }"
                                                >
                                                    <p class="font-mono text-[11px] font-semibold"
                                                        :class="{
                                                            'text-emerald-700': caso.badge === 'good',
                                                            'text-amber-700':   caso.badge === 'warn',
                                                            'text-red-700':     caso.badge === 'bad',
                                                        }"
                                                    >= {{ caso.resultado }}</p>
                                                    <span
                                                        class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                        :class="{
                                                            'bg-emerald-100 text-emerald-700': caso.badge === 'good',
                                                            'bg-amber-100   text-amber-700':   caso.badge === 'warn',
                                                            'bg-red-100     text-red-700':     caso.badge === 'bad',
                                                        }"
                                                    >{{ caso.label }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="row in datosFiltrados" :key="row.item" class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-2 py-2 text-center text-xs text-slate-400">{{ row.item }}</td>
                                <td class="px-3 py-2 text-xs text-slate-600">{{ row.NombreCentro }}</td>
                                <td class="px-3 py-2 text-xs text-slate-500">{{ row.Modelo }}</td>
                                <td class="px-3 py-2 text-center font-mono text-xs text-cyan-600">{{ row.Serie }}</td>
                                <td class="px-3 py-2 text-right text-xs font-bold text-violet-600 tabular-nums">{{ fmt(row.total) }}</td>
                                <td class="px-3 py-2 text-right text-xs font-bold text-amber-600 tabular-nums">{{ fmt(row.total_promedio_diario) }}</td>
                                <td class="px-3 py-2 text-right text-xs text-slate-400 tabular-nums">{{ fmt(row.promedioCentro) }}</td>
                                <td class="px-3 py-2 text-center text-xs font-bold tabular-nums" :class="colorVsHistorico(row.vs_historico)">
                                    <span v-if="row.vs_historico !== null">{{ row.vs_historico > 0 ? '+' : '' }}{{ row.vs_historico }}%</span>
                                    <span v-else class="text-slate-300">—</span>
                                </td>
                                <td class="px-3 py-2 text-center text-xs font-semibold text-emerald-600">{{ row.horas_con_produccion }}h</td>
                                <td class="px-3 py-2 text-center text-xs font-semibold text-blue-500">{{ row.horas_con_produccion_cero }}h</td>
                                <td class="px-3 py-2 text-center text-xs font-semibold text-red-500">{{ row.horas_sin_transmitir }}h</td>
                                <td class="px-3 py-2 text-center text-xs font-bold" :class="colorEficiencia(row.disponibilidad)">{{ row.disponibilidad }}%</td>
                                <td class="px-3 py-2 text-center text-xs font-bold" :class="colorEficiencia(row.rendimiento)">{{ row.rendimiento }}%</td>
                                <td class="px-3 py-2 text-center text-xs font-bold" :class="colorEficiencia(row.eficiencia_real)">{{ row.eficiencia_real }}%</td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="border-t-2 border-slate-200 bg-slate-50">
                                <td colspan="4" class="px-3 py-3 text-right text-xs font-black tracking-widest text-slate-500 uppercase">Total filtrado</td>
                                <td class="px-3 py-3 text-right text-sm font-black text-violet-600 tabular-nums">{{ fmt(totalMostrado) }}</td>
                                <td class="px-3 py-3 text-right text-sm font-black text-amber-600 tabular-nums">{{ fmt(promedioMostrado) }}</td>
                                <td class="px-3 py-3"></td>
                                <td class="px-3 py-3"></td>
                                <td class="px-3 py-3 text-center text-sm font-black text-emerald-600">{{ fmtInt(horasActivasMostradas) }}h</td>
                                <td class="px-3 py-3 text-center text-sm font-black text-blue-500">{{ fmtInt(horasCeroMostradas) }}h</td>
                                <td class="px-3 py-3 text-center text-sm font-black text-red-500">{{ fmtInt(horasMuertasMostradas) }}h</td>
                                <td class="px-3 py-3 text-center text-sm font-black" :class="colorEficiencia(parseFloat(disponibilidadMedia))">{{ disponibilidadMedia }}%</td>
                                <td class="px-3 py-3"></td>
                                <td class="px-3 py-3 text-center text-sm font-black" :class="colorEficiencia(parseFloat(eficienciaRealMedia))">{{ eficienciaRealMedia }}%</td>
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

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: all 0.18s ease;
}
.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>