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
const showCreateModal = ref(false);
const createForm = ref({
    serie: '',
    modelo: '',
    idCentro: 0,
    country: 0,
    relay: 0,
});
const centros = ref<{ IdCentro: number; NombreCentro: string }[]>([]);
const loadingCentros = ref(false);
const creating = ref(false);

const {
    showToast,
    toastMessage,
    toastType,
    showModal,
    modalData,
    showConfirm,
    confirmMessage,
    toast,
    openEditModal: _openEditModal,
    closeModal,
    confirmToggle,
    executeConfirm,
    cancelConfirm,
    saveModal: _saveModal,
} = useMaquinaModal(pais);

// ─── Campos extra que el composable no conoce ──────────────────────────
const CAMPOS_JUG = [
    { key: 'jugBill1',  label: 'Juego x10'  },
    { key: 'jugBill2',  label: 'Juego x20'  },
    { key: 'jugBill5',  label: 'Juego x50'  },
    { key: 'jugBill10', label: 'Juego x100' },
    { key: 'jugBill20', label: 'Juego x200' },
] as const;

const CAMPOS_MON = [
    { key: 'jugMon05', label: 'Juego x0.5' },
    { key: 'jugMon1',  label: 'Juego x1'   },
    { key: 'jugMon2',  label: 'Juego x2'   },
    { key: 'jugMon5',  label: 'Juego x5'   },
    { key: 'jugPOS',   label: 'Juego POS'  },
] as const;

// openEditModal extendido: copia los campos nuevos de row → modalData
function openEditModal(row: Record<string, any>) {
    _openEditModal(row);
    loadCentros();
    // next tick para asegurar que modalData ya fue asignado
    setTimeout(() => {
        if (!modalData.value) return;
        modalData.value.IdCentro   = row.IdCentro   ?? 0;
        modalData.value.mac        = row.mac        ?? '';
        modalData.value.jugBill1   = row.jugBill1   ?? '';
        modalData.value.jugBill2   = row.jugBill2   ?? '';
        modalData.value.jugBill5   = row.jugBill5   ?? '';
        modalData.value.jugBill10  = row.jugBill10  ?? '';
        modalData.value.jugBill20  = row.jugBill20  ?? '';
        modalData.value.jugMon05   = row.jugMon05   ?? '';
        modalData.value.jugMon1    = row.jugMon1    ?? '';
        modalData.value.jugMon2    = row.jugMon2    ?? '';
        modalData.value.jugMon5    = row.jugMon5    ?? '';
        modalData.value.jugPOS     = row.jugPOS     ?? '';
        modalData.value.pulsosPlay = row.pulsosPlay ?? '';
        modalData.value.valuePlay  = row.valuePlay  ?? '';
    }, 0);
}

// saveModal extendido: llama al composable Y envía los campos extras
async function saveModal() {
    // Dejamos que el composable haga su llamada normal
    await _saveModal();

    // Adicionalmente enviamos los campos nuevos por separado al mismo endpoint
    if (!modalData.value) return;

    const csrfToken =
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    const body = new URLSearchParams({
        idMaquina:  String(modalData.value.IdMaquina ?? ''),
        idCentro:   String(modalData.value.IdCentro  ?? ''),
        mac:        String(modalData.value.mac        ?? ''),
        jugBill1:   String(modalData.value.jugBill1   ?? ''),
        jugBill2:   String(modalData.value.jugBill2   ?? ''),
        jugBill5:   String(modalData.value.jugBill5   ?? ''),
        jugBill10:  String(modalData.value.jugBill10  ?? ''),
        jugBill20:  String(modalData.value.jugBill20  ?? ''),
        jugMon05:   String(modalData.value.jugMon05   ?? ''),
        jugMon1:    String(modalData.value.jugMon1    ?? ''),
        jugMon2:    String(modalData.value.jugMon2    ?? ''),
        jugMon5:    String(modalData.value.jugMon5    ?? ''),
        jugPOS:     String(modalData.value.jugPOS     ?? ''),
        pulsosPlay: String(modalData.value.pulsosPlay ?? ''),
        valuePlay:  String(modalData.value.valuePlay  ?? ''),
        esVisible:  String(modalData.value.esVisible  ?? 1),
        isRMT:      String(modalData.value.isRMT      ?? 0),
    });

    try {
        await fetch('/api/maquinas/actualizar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body,
        });
    } catch (e) {
        console.error('Error guardando campos extra:', e);
    }
}

if (props.error === 'serie_duplicada') {
    toast('El número de serie ya está asignado a otra máquina', 'error', 5000);
}
if (props.guardado && props.success) {
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
    router.get('/maquinas/visibles', { pais: pais.value }, { preserveScroll: true });
}

function formatTime(time: string | null): string {
    if (!time || typeof time !== 'string' || time.length < 5) return '--:--';
    return time.substring(0, 5);
}

function openCreateModal() {
    createForm.value = { serie: '', modelo: '', idCentro: 0, country: 0, relay: 0 };
    showCreateModal.value = true;
    loadCentros();
    loadCountries();
}

function closeCreateModal() {
    showCreateModal.value = false;
}

const countries = ref<{ IdCountry: number; Descripcion: string }[]>([]);

async function loadCentros() {
    loadingCentros.value = true;
    try {
        const res  = await fetch(`/api/maquinas/centros?pais=${pais.value}`);
        const data = await res.json();
        if (data.success) centros.value = data.data;
    } catch (e) {
        console.error(e);
    } finally {
        loadingCentros.value = false;
    }
}

async function loadCountries() {
    try {
        const res = await fetch('/api/maquinas/countries', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) { console.error('Error backend:', await res.text()); return; }
        const data = await res.json();
        if (data.success) countries.value = data.data;
    } catch (e) {
        console.error('Error cargando countries', e);
    }
}

function onCentroChange() {
    const selected = centros.value.find((c) => c.IdCentro === createForm.value.idCentro);
    if (selected) {
        const countryMap: Record<string, number> = {
            chile: 1, colombia: 2, australia: 7, peru: 3, provincia: 3,
        };
        createForm.value.country = countryMap[pais.value.toLowerCase()] || 3;
    }
}

async function crearMaquina() {
    if (!createForm.value.serie || !createForm.value.modelo || !createForm.value.idCentro) return;
    creating.value = true;
    const csrfToken =
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';
    try {
        const res = await fetch('/api/maquinas/crear', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: new URLSearchParams({
                serie:    createForm.value.serie,
                modelo:   createForm.value.modelo,
                idCentro: String(createForm.value.idCentro),
                country:  String(createForm.value.country),
                relay:    String(createForm.value.relay || 0),
            }),
        });
        const data = await res.json();
        if (data.success) {
            toast('Máquina creada correctamente', 'success');
            closeCreateModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            toast(data.message || 'Error al crear', 'error', 5000);
        }
    } catch (err: any) {
        toast('Error: ' + err.message, 'error', 5000);
    } finally {
        creating.value = false;
    }
}

function confirmarBaja(row: Record<string, any>) {
    confirmToggle(
        '¿Dar de baja esta máquina? Pasará a estado inactivo y no aparecerá en reportes.',
        () => ejecutarBaja(row.IdMaquina),
    );
}

async function ejecutarBaja(idMaquina: number) {
    const csrfToken =
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';
    try {
        const res  = await fetch('/api/maquinas/baja', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
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

function confirmarEliminar(row: Record<string, any>) {
    confirmToggle(
        `¿Eliminar permanentemente la máquina ${row.serie}? Esta acción no se puede deshacer.`,
        () => eliminarBajaFisica(row.IdMaquina),
    );
}

async function eliminarBajaFisica(idMaquina: number) {
    const csrfToken =
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';
    try {
        const res  = await fetch('/api/maquinas/eliminar-baja-fisica', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: new URLSearchParams({ idMaquina: String(idMaquina) }),
        });
        const data = await res.json();
        if (data.success) {
            toast('Máquina eliminada', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            toast(data.message || 'No se puede eliminar', 'error', 5000);
        }
    } catch (err: any) {
        toast('Error: ' + err.message, 'error', 5000);
    }
}
</script>

<template>
    <Head title="Máquinas" />
    <Layout>
        <div class="min-h-screen bg-slate-50 font-sans text-slate-700">
            <div class="mx-auto max-w-[1900px] px-2 py-4 sm:px-4 sm:py-6">
                <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-1 text-xs font-semibold tracking-[0.2em] text-indigo-500 uppercase">
                            Máquinas
                        </p>
                        <h1 class="text-2xl font-black tracking-tight text-slate-800 sm:text-3xl">
                            Listado <span class="text-indigo-400">Visibles</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ paisNombre[pais] || pais }} · {{ filteredCount }} de {{ maquinasCount }} máquinas
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="openCreateModal"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nueva Máquina
                        </button>
                        <select v-model="pais" @change="filtrar"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                            <option v-for="p in paises" :key="p" :value="p">{{ paisNombre[p] || p }}</option>
                        </select>
                        <div class="relative">
                            <svg class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input v-model="searchQuery" type="text" placeholder="Buscar Centro, Modelo o Serie..."
                                class="w-64 rounded-lg border border-slate-300 bg-white py-2 pr-3 pl-10 text-sm focus:border-indigo-500 focus:outline-none" />
                        </div>
                    </div>
                </header>

                <!-- Stats -->
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

                <!-- Tabla -->
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
                                <th class="w-16 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Baja Física</th>
                                <th class="w-16 px-3 py-3 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase">Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, idx) in filteredDatos" :key="row.IdMaquina"
                                class="border-b border-slate-100 hover:bg-slate-50">
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
                                    <span v-if="Number(row.esVisible) === 1"
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Visible</span>
                                    <span v-else
                                        class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700">No Visible</span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span v-if="Number(row.Relay) === 0"
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Verde</span>
                                    <span v-else
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Azul</span>
                                </td>
                                <td class="px-3 py-2.5 text-center font-mono text-xs text-slate-500">
                                    {{ row.lastReport ? row.lastReport.substring(0, 16) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span v-if="Number(row.isRMT ?? 0) === 0"
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Inactivo</span>
                                    <span v-else
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Activo</span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button @click="confirmarBaja(row)"
                                        class="rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-50" title="Dar de baja">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4l-8-4m8 4l8-4" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button @click="openEditModal(row)"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
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

        <!-- Toast -->
        <div v-if="showToast" :class="[
            'fixed right-6 bottom-6 z-[60] rounded-lg px-4 py-3 text-sm font-semibold text-white shadow-lg',
            toastType === 'error' ? 'bg-red-600' : 'bg-emerald-600',
        ]">{{ toastMessage }}</div>

        <!-- ══════════════════════════════════════════
             MODAL EDITAR — con scroll y campos nuevos
        ══════════════════════════════════════════ -->
        <!-- ══════════════════════════════════════
            DRAWER LATERAL — editar máquina
        ══════════════════════════════════════ -->

        <!-- Overlay sutil para cerrar clickeando afuera -->
        <Transition name="fade">
            <div
                v-if="showModal && modalData"
                class="fixed inset-0 z-40 bg-slate-900/20"
                @click="closeModal"
            />
        </Transition>

        <!-- Drawer -->
        <Transition name="slide-drawer">
            <div
                v-if="showModal && modalData"
                class="fixed top-0 right-0 z-50 flex h-full w-[380px] flex-col border-l border-slate-200 bg-white shadow-xl"
            >
                <!-- Header -->
                <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Editar máquina</h3>
                        <p class="mt-0.5 text-xs text-slate-400">
                            {{ modalData!.serie }} · {{ modalData!.centro }}
                        </p>
                    </div>
                    <button
                        @click="closeModal"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body scrollable -->
                <div class="flex-1 space-y-5 overflow-y-auto px-5 py-4">

                    <!-- Identificación -->
                <section>
                    <p class="mb-2 text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                        Identificación
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500">Centro</label>
                            <select v-model="modalData!.IdCentro"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none">
                                <option :value="0" disabled>Seleccionar centro...</option>
                                <option v-for="c in centros" :key="c.IdCentro" :value="c.IdCentro">
                                    {{ c.NombreCentro }}
                                </option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500">Modelo</label>
                                <input v-model="modalData!.modelo"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500">Serie</label>
                                <input v-model="modalData!.serie" disabled
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-mono text-slate-400 cursor-not-allowed" />
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500">MAC</label>
                            <input v-model="modalData!.mac" placeholder="xx:xx:xx:xx:xx:xx"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 font-mono text-sm focus:border-indigo-400 focus:outline-none" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500">Hora inicio</label>
                                <input v-model="modalData!.tON" type="time"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500">Hora fin</label>
                                <input v-model="modalData!.tOff" type="time"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none" />
                            </div>
                        </div>
                    </div>
                </section>

                    <!-- Juegos por billete -->
                    <section>
                        <p class="mb-2 text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                            Juegos por billete
                        </p>
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="campo in CAMPOS_JUG" :key="campo.key">
                                <label class="mb-1 block text-[11px] font-medium text-slate-500">
                                    {{ campo.label }}
                                </label>
                                <input v-model.number="modalData![campo.key]" type="number" min="0" step="0.01"
                                    class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs focus:border-indigo-400 focus:outline-none" />
                            </div>
                        </div>
                    </section>

                    <!-- Juegos por moneda / POS -->
                    <section>
                        <p class="mb-2 text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                            Juegos por moneda / POS
                        </p>
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="campo in CAMPOS_MON" :key="campo.key">
                                <label class="mb-1 block text-[11px] font-medium text-slate-500">
                                    {{ campo.label }}
                                </label>
                                <input v-model.number="modalData![campo.key]" type="number" min="0" step="0.01"
                                    class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs focus:border-indigo-400 focus:outline-none" />
                            </div>
                        </div>
                    </section>

                    <!-- Pulsos / Value -->
                    <section>
                        <p class="mb-2 text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                            Configuración de juego
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500">Pulsos play</label>
                                <input v-model.number="modalData!.pulsosPlay" type="number" min="0"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500">Value play</label>
                                <input v-model.number="modalData!.valuePlay" type="number" min="0" step="0.01"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none" />
                            </div>
                        </div>
                    </section>

                    <!-- Estado -->
                    <section>
                        <p class="mb-2 text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                            Estado
                        </p>
                        <div class="space-y-2">
                            <!-- Visibilidad -->
                            <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-xs font-medium text-slate-600">Visibilidad</p>
                                    <p class="text-[10px] text-slate-400">Aparece en reportes</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="['text-xs font-semibold', Number(modalData!.esVisible) ? 'text-emerald-600' : 'text-red-500']">
                                        {{ Number(modalData!.esVisible) ? 'Visible' : 'Oculta' }}
                                    </span>
                                    <button @click="confirmToggle(
                                        Number(modalData!.esVisible)
                                            ? '¿Ocultar esta máquina? Ya no aparecerá en los reportes.'
                                            : '¿Hacer visible esta máquina?',
                                        () => { modalData!.esVisible = Number(modalData!.esVisible) ? 0 : 1 }
                                    )"
                                    :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                        Number(modalData!.esVisible) ? 'bg-emerald-500' : 'bg-slate-300']">
                                        <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow transition-transform',
                                            Number(modalData!.esVisible) ? 'translate-x-4' : 'translate-x-0']" />
                                    </button>
                                </div>
                            </div>
                            <!-- RMT -->
                            <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-xs font-medium text-slate-600">RMT</p>
                                    <p class="text-[10px] text-slate-400">Reinicio automático</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="['text-xs font-semibold', Number(modalData!.isRMT) ? 'text-indigo-600' : 'text-slate-400']">
                                        {{ Number(modalData!.isRMT) ? 'Activo' : 'Inactivo' }}
                                    </span>
                                    <button @click="modalData!.isRMT = Number(modalData!.isRMT) ? 0 : 1"
                                        :class="['relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors',
                                            Number(modalData!.isRMT) ? 'bg-indigo-500' : 'bg-slate-300']">
                                        <span :class="['pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow transition-transform',
                                            Number(modalData!.isRMT) ? 'translate-x-4' : 'translate-x-0']" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Footer fijo -->
                <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                    <button @click="closeModal"
                        class="flex-1 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="button" @click="saveModal"
                        class="flex-[2] rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </Transition>

        <!-- ══════════════════════════════════════════
             MODAL CREAR — con scroll y campos nuevos
        ══════════════════════════════════════════ -->        

        <!-- Modal Crear -->
        <div v-if="showCreateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
            @click.self="closeCreateModal">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Nueva Máquina</h3>
                    <button @click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Serie</label>
                        <input v-model="createForm.serie" type="text" placeholder="Número de serie"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Modelo</label>
                        <input v-model="createForm.modelo" type="text" placeholder="Modelo de máquina"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Centro</label>
                        <select v-model="createForm.idCentro" @change="onCentroChange"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                            <option :value="0" disabled>Seleccionar centro...</option>
                            <option v-for="c in centros" :key="c.IdCentro" :value="c.IdCentro">
                                {{ c.NombreCentro }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Configuración</label>
                        <select v-model="createForm.country" :disabled="loadingCentros"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                            <option :value="0" disabled>
                                {{ loadingCentros ? 'Cargando...' : 'Seleccionar configuración...' }}
                            </option>
                            <option v-for="c in countries" :key="c.IdCountry" :value="c.IdCountry">
                                {{ c.Descripcion }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Relay</label>
                        <select v-model="createForm.relay"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                            <option :value="0">Verde (0)</option>
                            <option :value="1">Azul (1)</option>
                        </select>
                        <p class="mt-1 text-[10px] text-slate-400">
                            Verde = cable verde (relay 0) · Azul = cable azul (relay 1)
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="closeCreateModal"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="button" @click="crearMaquina"
                        :disabled="creating || !createForm.serie || !createForm.modelo || !createForm.idCentro"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                        {{ creating ? 'Creando...' : 'Crear' }}
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

.slide-drawer-enter-active,
.slide-drawer-leave-active { transition: transform .22s cubic-bezier(.4,0,.2,1); }
.slide-drawer-enter-from,
.slide-drawer-leave-to { transform: translateX(100%); }
</style>