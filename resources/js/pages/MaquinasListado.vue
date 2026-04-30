<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Layout from '../components/Layout.vue';
import { useMaquinaModal } from '../composables/useMaquinaModal';

const props = defineProps<{
    datos: any[];
    paises: string[];
    paisActual: string;
    error?: string;
    success?: string;
    guardado?: boolean;
}>();

const pais = ref(props.paisActual);
const searchQuery = ref('');

const {
    showToast, toastMessage, toastType,
    showModal, modalData,
    showConfirm, confirmMessage,
    toast, openEditModal, closeModal,
    confirmToggle, executeConfirm, cancelConfirm,
    saveModal,
} = useMaquinaModal(pais);

if (props.error === 'serie_duplicada') {
    toast('El número de serie ya está asignado a otra máquina', 'error', 5000);
}
if (props.guardado && props.success) {
    toast(props.success, 'success', 3000);
}

const paisNombre: Record<string, string> = {
    chile: 'Chile', colombia: 'Colombia', australia: 'Australia', peru: 'Perú',
};

const filteredDatos = computed(() => {
    if (!searchQuery.value) return props.datos;
    const q = searchQuery.value.toLowerCase();
    return props.datos.filter((row: any) =>
        row.centro?.toLowerCase().includes(q) ||
        row.modelo?.toLowerCase().includes(q) ||
        row.serie?.toLowerCase().includes(q),
    );
});

const maquinasCount = computed(() => props.datos.length);
const filteredCount = computed(() => filteredDatos.value.length);
const rmtCount      = computed(() =>
    filteredDatos.value.filter((r: any) => Number(r.isRMT) === 1).length,
);

function filtrar() {
    router.get('/maquinas/listado', { pais: pais.value }, { preserveScroll: true });
}

function formatTime(time: string | null): string {
    if (!time || typeof time !== 'string' || time.length < 5) return '--:--';
    return time.substring(0, 5);
}
</script>

<template>
    <Head title="Máquinas" />
    <Layout>
        <div class="min-h-screen bg-slate-50 font-sans text-slate-700">
            <div class="mx-auto max-w-[1900px] px-2 py-4 sm:px-4 sm:py-6">
                <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-1 text-xs font-semibold tracking-[0.2em] text-indigo-500 uppercase">Máquinas</p>
                        <h1 class="text-2xl font-black tracking-tight text-slate-800 sm:text-3xl">
                            Listado <span class="text-indigo-400">Visibles</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ paisNombre[pais] || pais }} · {{ filteredCount }} de {{ maquinasCount }} máquinas
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <select
                            v-model="pais"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            @change="filtrar"
                        >
                            <option v-for="p in paises" :key="p" :value="p">{{ paisNombre[p] || p }}</option>
                        </select>
                        <div class="relative">
                            <svg class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Buscar Centro, Modelo o Serie..."
                                class="w-64 rounded-lg border border-slate-300 bg-white py-2 pr-3 pl-10 text-sm focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </header>

                <div class="mb-4 grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <p class="text-[10px] tracking-wider text-slate-500 uppercase">Total</p>
                        <p class="mt-1 text-2xl font-black text-slate-700">{{ filteredCount }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-[10px] tracking-wider text-amber-600 uppercase">RMT Activo</p>
                        <p class="mt-1 text-2xl font-black text-amber-600">{{ rmtCount }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-[10px] tracking-wider text-emerald-600 uppercase">Visibles</p>
                        <p class="mt-1 text-2xl font-black text-emerald-600">{{ maquinasCount }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="w-10 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">#</th>
                                <th class="w-64 px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase">Centro</th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase">País</th>
                                <th class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase">Modelo</th>
                                <th class="w-24 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Versión</th>
                                <th class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Serie</th>
                                <th class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Horario</th>
                                <th class="w-24 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Visibilidad</th>
                                <th class="w-20 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Relay</th>
                                <th class="w-24 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Last Report</th>
                                <th class="w-20 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">RMT</th>
                                <th class="w-16 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, idx) in filteredDatos"
                                :key="row.IdMaquina"
                                class="border-b border-slate-100 hover:bg-slate-50"
                            >
                                <td class="px-3 py-2.5 text-center text-xs text-slate-400">{{ idx + 1 }}</td>
                                <td class="px-3 py-2.5 text-xs font-medium text-slate-600">{{ row.centro }}</td>
                                <td class="px-3 py-2.5 text-xs text-slate-500">{{ row.descripcionPais || '-' }}</td>
                                <td class="px-3 py-2.5 text-xs text-slate-600">{{ row.modelo }}</td>
                                <td class="px-3 py-2.5 text-center font-mono text-xs text-slate-500">{{ row.version || '-' }}</td>
                                <td class="px-3 py-2.5 text-center font-mono text-xs text-cyan-600">{{ row.serie }}</td>
                                <td class="px-3 py-2.5 text-center text-xs text-slate-500">
                                    {{ formatTime(row.tON) }} - {{ formatTime(row.tOff) }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span v-if="Number(row.esVisible) === 1" class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Visible</span>
                                    <span v-else class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700">No Visible</span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span v-if="Number(row.Relay) === 0" class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">0</span>
                                    <span v-else class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">{{ row.Relay }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-center font-mono text-xs text-slate-500">
                                    {{ row.lastReport ? row.lastReport.substring(0, 16) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span v-if="Number(row.isRMT ?? 0) === 0" class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Inactivo</span>
                                    <span v-else class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Activo</span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button
                                        @click="openEditModal(row)"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-500"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-if="!datos.length" class="py-16 text-center text-slate-400">
                    No hay máquinas para {{ paisNombre[pais] || pais }}.
                </p>
            </div>
        </div>

        <div
            v-if="showToast"
            :class="['fixed right-6 bottom-6 z-[60] rounded-lg px-4 py-3 text-sm font-semibold text-white shadow-lg', toastType === 'error' ? 'bg-red-600' : 'bg-emerald-600']"
        >
            {{ toastMessage }}
        </div>

        <div
            v-if="showModal && modalData"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
            @click.self="closeModal"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Editar Máquina</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Centro</label>
                        <input v-model="modalData!.centro" type="text" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Modelo</label>
                        <input v-model="modalData!.modelo" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Serie</label>
                        <input v-model="modalData!.serie" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Hora Inicio</label>
                            <input v-model="modalData!.tON" type="time" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Hora Fin</label>
                            <input v-model="modalData!.tOff" type="time" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-xs font-medium text-slate-600">Visibilidad</label>
                            <p class="text-[10px] text-slate-400">Si no es visible, no aparece en los reportes</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="['text-xs font-semibold', Number(modalData!.esVisible) ? 'text-emerald-600' : 'text-red-600']">
                                {{ Number(modalData!.esVisible) ? 'Visible' : 'No Visible' }}
                            </span>
                            <button
                                @click="confirmToggle(
                                    Number(modalData!.esVisible)
                                        ? '¿Ocultar esta máquina? Ya no aparecerá en los reportes.'
                                        : '¿Hacer visible esta máquina?',
                                    () => { modalData!.esVisible = Number(modalData!.esVisible) ? 0 : 1; }
                                )"
                                :class="['relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors', Number(modalData!.esVisible) ? 'bg-emerald-500' : 'bg-slate-200']"
                            >
                                <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform', Number(modalData!.esVisible) ? 'translate-x-5' : 'translate-x-0']" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-xs font-medium text-slate-600">RMT</label>
                            <p class="text-[10px] text-slate-400">Reinicio automático</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="['text-xs font-semibold', Number(modalData!.isRMT) ? 'text-blue-600' : 'text-emerald-600']">
                                {{ Number(modalData!.isRMT) ? 'Activo' : 'Inactivo' }}
                            </span>
                            <button
                                @click="modalData!.isRMT = Number(modalData!.isRMT) ? 0 : 1"
                                :class="['relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors', Number(modalData!.isRMT) ? 'bg-blue-500' : 'bg-slate-200']"
                            >
                                <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform', Number(modalData!.isRMT) ? 'translate-x-5' : 'translate-x-0']" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="closeModal" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
                    <button type="button" @click="saveModal" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar</button>
                </div>
            </div>
        </div>

        <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4">
                    <svg class="mx-auto h-10 w-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="mb-6 text-center text-slate-700">{{ confirmMessage }}</p>
                <div class="flex justify-end gap-3">
                    <button @click="cancelConfirm" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
                    <button @click="executeConfirm" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">Confirmar</button>
                </div>
            </div>
        </div>
    </Layout>
</template>