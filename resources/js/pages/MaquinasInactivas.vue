<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Layout from '../components/Layout.vue';

const props = defineProps<{
    datos: any[];
    paises: string[];
    paisActual: string;
    error?: string;
    success?: string;
    guardado?: boolean;
}>();

const pais = ref(props.paisActual);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error'>('success');
const showModal = ref(false);
const showConfirm = ref(false);
const showActivateModal = ref(false);
const confirmAction = ref<(() => void) | null>(null);
const confirmMessage = ref('');
const modalData = ref<Record<string, any> | null>(null);
const searchQuery = ref('');
const activateForm = ref({ idMaquina: 0, idCentro: 0, country: 0 });
const centros = ref<{ IdCentro: number; NombreCentro: string }[]>([]);
const loadingCentros = ref(false);
const activating = ref(false);

function toast(
    message: string,
    type: 'success' | 'error' = 'success',
    duration = 3000,
) {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, duration);
}

function confirmToggle(message: string, action: () => void) {
    confirmMessage.value = message;
    confirmAction.value = action;
    showConfirm.value = true;
}

function executeConfirm() {
    confirmAction.value?.();
    showConfirm.value = false;
    confirmAction.value = null;
}

function cancelConfirm() {
    showConfirm.value = false;
    confirmAction.value = null;
}

if (props.error === 'serie_duplicada') {
    toast('El número de serie ya está asignado a otra máquina', 'error', 5000);
}
if (props.guardado === true && props.success) {
    toast(props.success, 'success', 3000);
}

const paisNombre: Record<string, string> = {
    chile: 'Chile',
    colombia: 'Colombia',
    australia: 'Australia',
    peru: 'Perú',
};

const filteredDatos = computed(() => {
    if (!searchQuery.value) return props.datos;
    const q = searchQuery.value.toLowerCase();
    return props.datos.filter(
        (row: any) =>
            row.centro?.toLowerCase().includes(q) ||
            row.modelo?.toLowerCase().includes(q) ||
            row.serie?.toLowerCase().includes(q),
    );
});

const maquinasCount = computed(() => props.datos.length);
const filteredCount = computed(() => filteredDatos.value.length);
const rmtCount = computed(
    () => filteredDatos.value.filter((r: any) => Number(r.isRMT) === 1).length,
);

function filtrar() {
    router.get(
        '/maquinas/no-visibles',
        { pais: pais.value },
        { preserveScroll: true },
    );
}

function openEditModal(row: Record<string, any>) {
    modalData.value = { ...row };
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    modalData.value = null;
}

function saveModal() {
    if (!modalData.value) return;
    const data = modalData.value;

    const formData = new FormData();
    formData.append('idMaquina', String(data.IdMaquina));
    formData.append('idCentro', String(data.IdCentro));
    formData.append('modelo', data.modelo ?? '');
    formData.append('serie', data.serie ?? '');
    formData.append('tON', data.tON ?? '');
    formData.append('tOff', data.tOff ?? '');
    formData.append('esVisible', String(Number(data.esVisible) ? 1 : 0));
    formData.append('isRMT', String(Number(data.isRMT) ? 1 : 0));
    formData.append('pais', pais.value);

    const csrfToken =
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    fetch('/api/maquinas/actualizar', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
        body: formData,
    })
        .then((res) => res.json())
        .then((res) => {
            if (res.success) {
                toast('Cambios guardados correctamente', 'success');
                closeModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                toast(res.message ?? 'Error al guardar', 'error', 5000);
            }
        })
        .catch((err) => toast('Error de red: ' + err.message, 'error', 5000));
}

function formatTime(time: string | null): string {
    if (!time || typeof time !== 'string' || time.length < 5) return '--:--';
    return time.substring(0, 5);
}

function openActivateModal(row: Record<string, any>) {
    activateForm.value = { idMaquina: row.IdMaquina, idCentro: 0, country: 0 };
    showActivateModal.value = true;
    loadCentros();
}

function closeActivateModal() {
    showActivateModal.value = false;
}

async function loadCentros() {
    loadingCentros.value = true;
    try {
        const res = await fetch(`/api/maquinas/centros?pais=${pais.value}`);
        const data = await res.json();
        if (data.success) {
            centros.value = data.data;
        }
    } catch (e) {
        console.error(e);
    } finally {
        loadingCentros.value = false;
    }
}

function onCentroChange() {
    const selected = centros.value.find(
        (c) => c.IdCentro === activateForm.value.idCentro,
    );
    if (selected) {
        const paisLower = pais.value.toLowerCase();
        const countryMap: Record<string, number> = {
            chile: 1,
            colombia: 2,
            australia: 7,
            peru: 3,
            provincia: 3,
        };
        activateForm.value.country = countryMap[paisLower] || 3;
    }
}

async function activarMaquina() {
    if (!activateForm.value.idCentro || !activateForm.value.country) return;

    activating.value = true;
    const csrfToken =
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    try {
        const res = await fetch('/api/maquinas/activar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: new URLSearchParams({
                idMaquina: String(activateForm.value.idMaquina),
                idCentro: String(activateForm.value.idCentro),
                country: String(activateForm.value.country),
            }),
        });
        const data = await res.json();

        if (data.success) {
            toast('Máquina activada correctamente', 'success');
            closeActivateModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            toast(data.message || 'Error al activar', 'error', 5000);
        }
    } catch (err: any) {
        toast('Error: ' + err.message, 'error', 5000);
    } finally {
        activating.value = false;
    }
}

function confirmarBaja(row: Record<string, any>) {
    confirmToggle(
        '¿Dar de baja esta máquina? Pasará a baja física y no aparecerá en reportes.',
        () => ejecutarBaja(row.IdMaquina),
    );
}

async function ejecutarBaja(idMaquina: number) {
    const csrfToken =
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    try {
        const res = await fetch('/api/maquinas/baja', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: new URLSearchParams({ idMaquina: String(idMaquina) }),
        });
        const data = await res.json();

        if (data.success) {
            toast('Máquina dada de baja', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            toast(data.message || 'Error al dar baja', 'error', 5000);
        }
    } catch (err: any) {
        toast('Error: ' + err.message, 'error', 5000);
    }
}

function confirmarEliminarStaging(row: Record<string, any>) {
    confirmToggle(
        '¿Eliminar esta máquina de staging? Solo se puede hacer si no tiene producción.',
        () => ejecutarEliminarStaging(row.IdMaquina),
    );
}

async function ejecutarEliminarStaging(idMaquina: number) {
    const csrfToken =
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    try {
        const res = await fetch('/api/maquinas/eliminar-staging', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: new URLSearchParams({ idMaquina: String(idMaquina) }),
        });
        const data = await res.json();

        if (data.success) {
            toast('Máquina eliminada del staging', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            toast(data.message || 'Error al eliminar', 'error', 5000);
        }
    } catch (err: any) {
        toast('Error: ' + err.message, 'error', 5000);
    }
}
</script>

<template>
    <Head title="Máquinas No Visibles" />
    <Layout>
        <div class="min-h-screen bg-slate-50 font-sans text-slate-700">
            <div class="mx-auto max-w-[1900px] px-2 py-4 sm:px-4 sm:py-6">
                <header
                    class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-semibold tracking-[0.2em] text-red-500 uppercase"
                        >
                            Máquinas
                        </p>
                        <h1
                            class="text-2xl font-black tracking-tight text-slate-800 sm:text-3xl"
                        >
                            Listado
                            <span class="text-red-400">No Visibles</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ paisNombre[pais] || pais }} ·
                            {{ filteredCount }} de {{ maquinasCount }} máquinas
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <select
                            v-model="pais"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            @change="filtrar"
                        >
                            <option v-for="p in paises" :key="p" :value="p">
                                {{ paisNombre[p] || p }}
                            </option>
                        </select>
                        <div class="relative">
                            <svg
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
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
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-slate-500 uppercase"
                        >
                            Total
                        </p>
                        <p class="mt-1 text-2xl font-black text-slate-700">
                            {{ filteredCount }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-amber-200 bg-amber-50 p-4"
                    >
                        <p
                            class="text-[10px] tracking-wider text-amber-600 uppercase"
                        >
                            RMT Activo
                        </p>
                        <p class="mt-1 text-2xl font-black text-amber-600">
                            {{ rmtCount }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <p
                            class="text-[10px] tracking-wider text-red-600 uppercase"
                        >
                            No Visibles
                        </p>
                        <p class="mt-1 text-2xl font-black text-red-600">
                            {{ maquinasCount }}
                        </p>
                    </div>
                </div>

                <div
                    class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th
                                    class="w-10 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    #
                                </th>
                                <th
                                    class="w-64 px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Centro
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    País
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Modelo
                                </th>
                                <th
                                    class="w-24 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Versión
                                </th>
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Serie
                                </th>
                                <th
                                    class="px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Horario
                                </th>
                                <th
                                    class="w-24 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Visibilidad
                                </th>
                                <th
                                    class="w-20 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Relay
                                </th>
                                <th
                                    class="w-24 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Last Report
                                </th>
                                <th
                                    class="w-20 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    RMT
                                </th>
                                <th
                                    class="w-16 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Activar
                                </th>
                                <th
                                    class="w-16 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Baja
                                </th>
                                <th
                                    class="w-16 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Eliminar
                                </th>
                                <th
                                    class="w-16 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Editar
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, idx) in filteredDatos"
                                :key="row.IdMaquina"
                                class="border-b border-slate-100 hover:bg-slate-50"
                            >
                                <td
                                    class="px-3 py-2.5 text-center text-xs text-slate-400"
                                >
                                    {{ idx + 1 }}
                                </td>
                                <td
                                    class="px-3 py-2.5 text-xs font-medium text-slate-600"
                                >
                                    {{ row.centro }}
                                </td>
                                <td class="px-3 py-2.5 text-xs text-slate-500">
                                    {{ row.descripcionPais || '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-xs text-slate-600">
                                    {{ row.modelo }}
                                </td>
                                <td
                                    class="px-3 py-2.5 text-center font-mono text-xs text-slate-500"
                                >
                                    {{ row.version || '-' }}
                                </td>
                                <td
                                    class="px-3 py-2.5 text-center font-mono text-xs text-cyan-600"
                                >
                                    {{ row.serie }}
                                </td>
                                <td
                                    class="px-3 py-2.5 text-center text-xs text-slate-500"
                                >
                                    {{ formatTime(row.tON) }} -
                                    {{ formatTime(row.tOff) }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span
                                        v-if="Number(row.esVisible) === 1"
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                        >Visible</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700"
                                        >No Visible</span
                                    >
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span
                                        v-if="Number(row.Relay) === 0"
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                        >0</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700"
                                        >{{ row.Relay }}</span
                                    >
                                </td>
                                <td
                                    class="px-3 py-2.5 text-center font-mono text-xs text-slate-500"
                                >
                                    {{
                                        row.lastReport
                                            ? row.lastReport.substring(0, 16)
                                            : '-'
                                    }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span
                                        v-if="Number(row.isRMT ?? 0) === 0"
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                        >Inactivo</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700"
                                        >Activo</span
                                    >
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button
                                        @click="openActivateModal(row)"
                                        class="rounded-lg p-1.5 text-green-600 transition-colors hover:bg-green-50"
                                        title="Activar máquina"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button
                                        @click="confirmarBaja(row)"
                                        class="rounded-lg p-1.5 text-red-600 transition-colors hover:bg-red-50"
                                        title="Dar de baja"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4l-8-4m8 4l8-4"
                                            />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button
                                        @click="confirmarEliminarStaging(row)"
                                        class="rounded-lg p-1.5 text-orange-600 transition-colors hover:bg-orange-50"
                                        title="Eliminar de staging"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button
                                        @click="openEditModal(row)"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-500"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"
                                            />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p
                    v-if="!datos.length"
                    class="py-16 text-center text-slate-400"
                >
                    No hay máquinas no visibles para
                    {{ paisNombre[pais] || pais }}.
                </p>
            </div>
        </div>

        <div
            v-if="showToast"
            :class="[
                'fixed right-6 bottom-6 z-[60] rounded-lg px-4 py-3 text-sm font-semibold text-white shadow-lg',
                toastType === 'error' ? 'bg-red-600' : 'bg-emerald-600',
            ]"
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
                    <h3 class="text-lg font-bold text-slate-800">
                        Editar Máquina
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-slate-400 hover:text-slate-600"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-600"
                            >Centro</label
                        >
                        <input
                            v-model="modalData!.centro"
                            type="text"
                            readonly
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-600"
                            >Modelo</label
                        >
                        <input
                            v-model="modalData!.modelo"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-600"
                            >Serie</label
                        >
                        <input
                            v-model="modalData!.serie"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-600"
                                >Hora Inicio</label
                            >
                            <input
                                v-model="modalData!.tON"
                                type="time"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-600"
                                >Hora Fin</label
                            >
                            <input
                                v-model="modalData!.tOff"
                                type="time"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-xs font-medium text-slate-600"
                                >Visibilidad</label
                            >
                            <p class="text-[10px] text-slate-400">
                                Activar para que aparezca en reportes
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                :class="[
                                    'text-xs font-semibold',
                                    Number(modalData!.esVisible)
                                        ? 'text-emerald-600'
                                        : 'text-red-600',
                                ]"
                            >
                                {{
                                    Number(modalData!.esVisible)
                                        ? 'Visible'
                                        : 'No Visible'
                                }}
                            </span>
                            <button
                                @click="
                                    confirmToggle(
                                        Number(modalData!.esVisible)
                                            ? '¿Ocultar esta máquina? Ya no aparecerá en los reportes.'
                                            : '¿Hacer visible esta máquina?',
                                        () => {
                                            modalData!.esVisible = Number(
                                                modalData!.esVisible,
                                            )
                                                ? 0
                                                : 1;
                                        },
                                    )
                                "
                                :class="[
                                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                    Number(modalData!.esVisible)
                                        ? 'bg-emerald-500'
                                        : 'bg-slate-200',
                                ]"
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform',
                                        Number(modalData!.esVisible)
                                            ? 'translate-x-5'
                                            : 'translate-x-0',
                                    ]"
                                />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-xs font-medium text-slate-600"
                                >RMT</label
                            >
                            <p class="text-[10px] text-slate-400">
                                Reinicio automático
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                :class="[
                                    'text-xs font-semibold',
                                    Number(modalData!.isRMT)
                                        ? 'text-blue-600'
                                        : 'text-emerald-600',
                                ]"
                            >
                                {{
                                    Number(modalData!.isRMT)
                                        ? 'Activo'
                                        : 'Inactivo'
                                }}
                            </span>
                            <button
                                @click="
                                    modalData!.isRMT = Number(modalData!.isRMT)
                                        ? 0
                                        : 1
                                "
                                :class="[
                                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                    Number(modalData!.isRMT)
                                        ? 'bg-blue-500'
                                        : 'bg-slate-200',
                                ]"
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform',
                                        Number(modalData!.isRMT)
                                            ? 'translate-x-5'
                                            : 'translate-x-0',
                                    ]"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="closeModal"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        @click="saveModal"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                    >
                        Guardar
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="showConfirm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
        >
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4">
                    <svg
                        class="mx-auto h-10 w-10 text-amber-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                </div>
                <p class="mb-6 text-center text-slate-700">
                    {{ confirmMessage }}
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="cancelConfirm"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="executeConfirm"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="showActivateModal && activateForm.idMaquina"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
            @click.self="closeActivateModal"
        >
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">
                        Activar Máquina
                    </h3>
                    <button
                        @click="closeActivateModal"
                        class="text-slate-400 hover:text-slate-600"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-600"
                            >Centro</label
                        >
                        <select
                            v-model="activateForm.idCentro"
                            @change="onCentroChange"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                        >
                            <option :value="0" disabled>
                                Seleccionar centro...
                            </option>
                            <option
                                v-for="c in centros"
                                :key="c.IdCentro"
                                :value="c.IdCentro"
                            >
                                {{ c.NombreCentro }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-600"
                            >Country ID</label
                        >
                        <input
                            v-model="activateForm.country"
                            type="number"
                            readonly
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500"
                        />
                        <p class="mt-1 text-[10px] text-slate-400">
                            Se asigna automáticamente según el centro
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="closeActivateModal"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        @click="activarMaquina"
                        :disabled="activating || !activateForm.idCentro"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500 disabled:opacity-50"
                    >
                        {{ activating ? 'Activando...' : 'Activar' }}
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>
