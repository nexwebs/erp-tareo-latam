<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Layout from '../components/Layout.vue';

const props = defineProps<{
    datos: any[];
    zonas: any[];
    paisActual: string;
    modoTodos?: boolean;
}>();

const pais = ref(props.paisActual || 'peru');
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error'>('success');
const showModal = ref(false);
const modalData = ref<Record<string, any> | null>(null);
const searchQuery = ref('');
const creating = ref(false);
const nuevasZonas = ref<{ id: number; descripcion: string }[]>([]);
const todosVisible = ref(props.modoTodos || false);

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

const paisNombre: Record<string, string> = {
    chile: 'Chile',
    colombia: 'Colombia',
    australia: 'Australia',
    peru: 'Perú',
    provincia: 'Provincia',
    todos: 'Todos',
};

const zonaMap = computed(() => {
    const map: Record<number, string> = {};
    [...(props.zonas || []), ...nuevasZonas.value].forEach((z) => {
        map[z.id] = z.descripcion;
    });
    return map;
});

const filteredDatos = computed(() => {
    if (!searchQuery.value) return props.datos || [];
    const q = searchQuery.value.toLowerCase();
    return (props.datos || []).filter((row: any) =>
        row.NombreCentro?.toLowerCase().includes(q),
    );
});

const filteredCount = computed(() => filteredDatos.value.length);

function filtrar() {
    const params: Record<string, any> = { pais: pais.value };
    if (todosVisible.value) params.todos = '1';
    router.get(
        todosVisible.value ? '/centros/todos' : '/centros/gestion',
        params,
        { preserveScroll: true },
    );
}

function toggleTodos() {
    todosVisible.value = !todosVisible.value;
    filtrar();
}

function formatTime(time: string | null): string {
    if (!time || typeof time !== 'string' || time.length < 5) return '--:--';
    return time.substring(0, 5);
}

function openCreateModal() {
    modalData.value = {
        nombre: '',
        pais: pais.value === 'todos' ? 'peru' : pais.value,
        idZona: 0,
        tOn: '08:00:00',
        tOff: '23:00:00',
    };
    showModal.value = true;
}

function openEditModal(row: any) {
    modalData.value = { ...row };
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    modalData.value = null;
}

async function crearCentro() {
    if (!modalData.value?.nombre || !modalData.value?.idZona) return;
    creating.value = true;
    const csrfToken =
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    try {
        const res = await fetch('/api/centros/crear', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: new URLSearchParams({
                nombre: modalData.value.nombre,
                pais: modalData.value.pais,
                idZona: String(modalData.value.idZona),
                tOn: modalData.value.tOn || '08:00:00',
                tOff: modalData.value.tOff || '23:00:00',
            }),
        });
        const data = await res.json();
        if (data.success) {
            toast('Centro creado correctamente', 'success');
            closeModal();
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

async function actualizarCentro() {
    if (!modalData.value?.nombre || !modalData.value?.IdCentro) return;
    creating.value = true;
    const csrfToken =
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    try {
        const res = await fetch('/api/centros/actualizar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: new URLSearchParams({
                idCentro: String(modalData.value.IdCentro),
                nombre: modalData.value.nombre,
                tOn: modalData.value.tOn || '08:00:00',
                tOff: modalData.value.tOff || '23:00:00',
            }),
        });
        const data = await res.json();
        if (data.success) {
            toast('Centro actualizado correctamente', 'success');
            closeModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            toast(data.message || 'Error al actualizar', 'error', 5000);
        }
    } catch (err: any) {
        toast('Error: ' + err.message, 'error', 5000);
    } finally {
        creating.value = false;
    }
}

function confirmarBaja(row: any) {
    if (!confirm('¿Dar de baja este centro?')) return;
    const csrfToken =
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';
    fetch('/api/centros/baja', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
        body: new URLSearchParams({ idCentro: String(row.IdCentro) }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                toast('Centro dado de baja', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                toast(data.message || 'Error al dar baja', 'error', 5000);
            }
        })
        .catch((err) => toast('Error: ' + err.message, 'error', 5000));
}

async function crearZonaRapida(descripcion: string) {
    if (!descripcion) return;
    const csrfToken =
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';
    try {
        const res = await fetch('/api/centros/zonas/crear', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: new URLSearchParams({ descripcion }),
        });
        const data = await res.json();
        if (data.success) {
            toast('Zona creada: ' + descripcion, 'success');
            nuevasZonas.value = [...nuevasZonas.value, data.data];
            if (modalData.value) modalData.value.idZona = data.data.id;
        } else {
            toast(data.message || 'Error al crear zona', 'error', 5000);
        }
    } catch (err: any) {
        toast('Error: ' + err.message, 'error', 5000);
    }
}
</script>

<template>
    <Head title="Centros" />
    <Layout>
        <div class="min-h-screen bg-slate-50 font-sans text-slate-700">
            <div class="mx-auto max-w-[1900px] px-2 py-4 sm:px-4 sm:py-6">
                <header
                    class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="mb-1 text-xs font-semibold tracking-[0.2em] text-indigo-500 uppercase"
                        >
                            Mantenimiento
                        </p>
                        <h1
                            class="text-2xl font-black tracking-tight text-slate-800 sm:text-3xl"
                        >
                            Centros <span class="text-indigo-400">y Zonas</span>
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ paisNombre[pais] || pais }} ·
                            {{ filteredCount }} centros
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="toggleTodos"
                            :class="[
                                'rounded-lg px-3 py-2 text-sm font-semibold',
                                todosVisible
                                    ? 'bg-amber-500 text-white'
                                    : 'bg-slate-200 text-slate-700',
                            ]"
                        >
                            {{ todosVisible ? 'Solo Activos' : 'Ver Todos' }}
                        </button>
                        <button
                            type="button"
                            @click="openCreateModal"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                        >
                            <svg
                                class="mr-1.5 h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Nuevo Centro
                        </button>
                        <select
                            v-model="pais"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            @change="filtrar"
                        >
                            <option value="peru">{{ paisNombre.peru }}</option>
                            <option value="chile">
                                {{ paisNombre.chile }}
                            </option>
                            <option value="colombia">
                                {{ paisNombre.colombia }}
                            </option>
                            <option value="australia">
                                {{ paisNombre.australia }}
                            </option>
                            <option value="provincia">
                                {{ paisNombre.provincia }}
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
                                placeholder="Buscar centro..."
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
                </div>

                <div
                    class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th
                                    class="w-10 px-2 py-2 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    #
                                </th>
                                <th
                                    class="px-2 py-2 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Centro
                                </th>
                                <th
                                    class="px-2 py-2 text-left text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Zona
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    País
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Horario
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Owner
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Color1
                                </th>
                                <th
                                    class="px-2 py-2 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Color2
                                </th>
                                <th
                                    class="w-20 px-2 py-2 text-center text-[10px] font-bold tracking-widest text-slate-500 uppercase"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, idx) in filteredDatos"
                                :key="row.IdCentro"
                                class="border-b border-slate-100 hover:bg-slate-50"
                            >
                                <td
                                    class="px-2 py-2 text-center text-xs text-slate-400"
                                >
                                    {{ idx + 1 }}
                                </td>
                                <td
                                    class="px-2 py-2 text-xs font-medium text-slate-600"
                                >
                                    {{ row.NombreCentro }}
                                </td>
                                <td class="px-2 py-2 text-xs text-slate-500">
                                    {{ zonaMap[row.zona] || row.zona || '-' }}
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <span
                                        v-if="row.EsChile"
                                        class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                        >Chile</span
                                    >
                                    <span
                                        v-else-if="row.EsColombia"
                                        class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700"
                                        >Colombia</span
                                    >
                                    <span
                                        v-else-if="row.EsAustralia"
                                        class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700"
                                        >Australia</span
                                    >
                                    <span
                                        v-else-if="row.EsProvincia"
                                        class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold text-purple-700"
                                        >Provincia</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700"
                                        >Perú</span
                                    >
                                </td>
                                <td
                                    class="px-2 py-2 text-center text-xs text-slate-500"
                                >
                                    {{ formatTime(row.tOn) }}-{{
                                        formatTime(row.tOff)
                                    }}
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <span
                                        v-if="row.EsActivo"
                                        class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                        >Activo</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700"
                                        >Inactivo</span
                                    >
                                </td>
                                <td
                                    class="px-2 py-2 text-center text-xs text-slate-500"
                                >
                                    {{ row.owner || '-' }}
                                </td>
                                <td
                                    class="px-2 py-2 text-center font-mono text-xs"
                                    :style="{ color: row.Color1 }"
                                >
                                    {{ row.Color1 || '-' }}
                                </td>
                                <td
                                    class="px-2 py-2 text-center font-mono text-xs"
                                    :style="{ color: row.Color2 }"
                                >
                                    {{ row.Color2 || '-' }}
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <button
                                        @click="openEditModal(row)"
                                        class="rounded p-1 text-slate-400 hover:text-indigo-500"
                                        title="Editar"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        v-if="row.EsActivo"
                                        @click="confirmarBaja(row)"
                                        class="ml-1 rounded p-1 text-slate-400 hover:text-red-500"
                                        title="Dar de baja"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4l-8-4m8 4l8-4"
                                            />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p
                    v-if="!datos?.length"
                    class="py-16 text-center text-slate-400"
                >
                    No hay centros para {{ paisNombre[pais] || pais }}.
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
            <div
                class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-6 shadow-xl"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">
                        {{
                            modalData.IdCentro
                                ? 'Editar Centro'
                                : 'Nuevo Centro'
                        }}
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
                            >Nombre</label
                        >
                        <input
                            v-model="modalData.nombre"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-600"
                            >País</label
                        >
                        <select
                            v-model="modalData.pais"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        >
                            <option value="peru">Perú</option>
                            <option value="chile">Chile</option>
                            <option value="colombia">Colombia</option>
                            <option value="australia">Australia</option>
                            <option value="provincia">Provincia</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-slate-600"
                            >Zona</label
                        >
                        <div class="flex gap-2">
                            <select
                                v-model="modalData.idZona"
                                class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            >
                                <option :value="0" disabled>
                                    Seleccionar zona...
                                </option>
                                <option
                                    v-for="z in [
                                        ...(props.zonas || []),
                                        ...nuevasZonas,
                                    ]"
                                    :key="z.id"
                                    :value="z.id"
                                >
                                    {{ z.descripcion }}
                                </option>
                            </select>
                            <button
                                type="button"
                                @click="
                                    crearZonaRapida(prompt('Nueva zona:') || '')
                                "
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50"
                                title="Crear zona"
                            >
                                +
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-600"
                                >Hora Inicio</label
                            >
                            <input
                                v-model="modalData.tOn"
                                type="time"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-slate-600"
                                >Hora Fin</label
                            >
                            <input
                                v-model="modalData.tOff"
                                type="time"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
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
                        @click="
                            modalData.IdCentro
                                ? actualizarCentro()
                                : crearCentro()
                        "
                        :disabled="
                            creating || !modalData.nombre || !modalData.idZona
                        "
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{
                            creating
                                ? 'Guardando...'
                                : modalData.IdCentro
                                  ? 'Actualizar'
                                  : 'Crear'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>
