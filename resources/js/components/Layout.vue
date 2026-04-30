<template>
    <div
        class="flex min-h-screen bg-slate-50 font-sans text-slate-700 antialiased"
    >
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex flex-col border-r border-slate-200 bg-white shadow-sm transition-all duration-300 ease-in-out"
            :class="sidebarOpen ? 'w-64' : 'w-0 lg:w-[72px]'"
        >
            <!-- Logo Area -->
            <div
                class="relative z-10 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4"
            >
                <div
                    class="flex flex-1 cursor-pointer items-center gap-3 overflow-hidden"
                    @click="sidebarOpen = false"
                    :class="{ 'justify-center': !sidebarOpen }"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 font-bold text-white shadow-lg shadow-emerald-500/20"
                    >
                        P
                    </div>
                    <div v-show="sidebarOpen" class="truncate">
                        <h1
                            class="truncate text-[14px] font-semibold tracking-tight text-slate-700"
                        >
                            Planet ERP
                        </h1>
                        <p
                            class="truncate text-[9px] font-medium tracking-widest text-emerald-600 uppercase"
                        >
                           Consolidado Latam
                        </p>
                    </div>
                </div>
                <button
                    @click.stop="sidebarOpen = !sidebarOpen"
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-slate-300 bg-slate-100 text-slate-500 transition-colors hover:bg-slate-200 hover:text-slate-700"
                    :class="{
                        'absolute top-5 right-[-12px] z-50 border-slate-300 bg-white':
                            !sidebarOpen,
                    }"
                >
                    <svg
                        v-if="sidebarOpen"
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                        />
                    </svg>
                    <svg
                        v-else
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M13 5l7 7-7 7M5 5l7 7-7 7"
                        />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <NavItem
                    href="/dashboard"
                    icon="home"
                    :collapsed="!sidebarOpen"
                    :active="isActive('/dashboard')"
                    >Dashboard</NavItem
                >

                <div
                    v-if="sidebarOpen"
                    class="mt-3 border-t border-slate-200 pt-3 pb-2 pl-3"
                >
                    <p
                        class="text-[10px] font-medium tracking-widest text-slate-500 uppercase"
                    >
                        Producción
                    </p>
                </div>
                <div v-else class="mx-2 my-3 h-px bg-slate-200"></div>

                <NavGroup
                    icon="calculator"
                    label="Producción"
                    :collapsed="!sidebarOpen"
                    :defaultOpen="isGroupActive('produccion')"
                    @expand="expandSidebar"
                >
                    <NavSubItem
                        href="/produccion/australia"
                        :active="isActive('/produccion/australia')"
                        >Australia</NavSubItem
                    >
                    <NavSubItem
                        href="/produccion/chile"
                        :active="isActive('/produccion/chile')"
                        >Chile</NavSubItem
                    >
                    <NavSubItem
                        href="/produccion/colombia"
                        :active="isActive('/produccion/colombia')"
                        >Colombia</NavSubItem
                    >
                    <NavSubItem
                        href="/produccion/peru"
                        :active="isActive('/produccion/peru')"
                        >Perú</NavSubItem
                    >

                    <div class="mx-3 my-1 h-px bg-slate-200"></div>

                    <NavSubItem
                        href="/produccion/eficiencia"
                        :active="isActive('/produccion/eficiencia')"
                        >Eficiencia</NavSubItem
                    >
                    <div class="mx-3 my-1 h-px bg-slate-200"></div>
                </NavGroup>

                <NavGroup
                    icon="clipboard"
                    label="Máquinas"
                    :collapsed="!sidebarOpen"
                    :defaultOpen="isGroupActive('maquinas')"
                    @expand="expandSidebar"
                >
                    <NavSubItem
                        href="/maquinas/visibles"
                        :active="isActive('/maquinas/visibles')"
                        >Visibles</NavSubItem
                    >
                    <NavSubItem
                        href="/maquinas/no-visibles"
                        :active="isActive('/maquinas/no-visibles')"
                        >No visibles</NavSubItem
                    >
                    <NavSubItem
                        href="/maquinas/baja-fisica"
                        :active="isActive('/maquinas/baja-fisica')"
                        >Baja física</NavSubItem
                    >
                </NavGroup>

                <NavGroup
                    icon="clipboard"
                    label="Mantenimiento"
                    :collapsed="!sidebarOpen"
                    :defaultOpen="isGroupActive('mantenimiento')"
                    @expand="expandSidebar"
                >
                    <NavSubItem
                        href="/centros/gestion"
                        :active="isActive('/centros/gestion')"
                        >Centros</NavSubItem
                    >
                </NavGroup>

                <!-- <NavGroup
                    icon="chart"
                    label="Reportes"
                    :collapsed="!sidebarOpen"
                    :defaultOpen="isGroupActive('reportes')"
                >
                    <NavSubItem
                        href="/reportes/diario"
                        :active="isActive('/reportes/diario')"
                        >Reporte Diario</NavSubItem
                    >
                    <NavSubItem
                        href="/reportes/historico"
                        :active="isActive('/reportes/historico')"
                        >Histórico</NavSubItem
                    >
                    <NavSubItem
                        href="/reportes/centros"
                        :active="isActive('/reportes/centros')"
                        >Por Centros</NavSubItem
                    >
                    <NavSubItem
                        href="/reportes/comparativo"
                        :active="isActive('/reportes/comparativo')"
                        >Comparativo</NavSubItem
                    >
                </NavGroup> -->
            </nav>

            <!-- User Profile -->
            <div
                class="flex-shrink-0 border-t border-slate-200 bg-slate-50 transition-all duration-300"
            >
                <div v-if="sidebarOpen" class="flex flex-col gap-3 p-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 font-semibold text-slate-600"
                        >
                            {{ userInitials }}
                        </div>
                        <div class="min-w-0 flex-1 pr-1">
                            <p
                                class="truncate text-[12px] leading-tight font-medium text-slate-700"
                            >
                                {{ userName }}
                            </p>
                            <p
                                class="mt-0.5 text-[10px] font-medium text-emerald-600"
                            >
                                {{ userRole }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="logout"
                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-medium text-slate-500 transition-all hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600"
                    >
                        Cerrar Sesión
                    </button>
                </div>
                <div v-else class="p-2">
                    <div
                        class="mb-2 flex aspect-square w-full items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-xs font-medium text-slate-500"
                    >
                        {{ userInitials }}
                    </div>
                    <button
                        @click="logout"
                        class="flex w-full justify-center rounded-lg border border-transparent py-2 text-slate-400 transition-colors hover:border-rose-300 hover:bg-rose-50 hover:text-rose-500"
                        title="Cerrar sesión"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Overlay mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-slate-950/70 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Main Content -->
        <div
            class="relative flex min-h-screen min-w-0 flex-1 flex-col transition-all duration-300"
            :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-[72px]'"
        >
            <!-- Header -->
            <header
                class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-slate-200 bg-white/80 px-4 backdrop-blur-sm sm:px-5"
            >
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="mr-2 rounded-md bg-slate-100 p-2 text-slate-500 hover:text-emerald-600 lg:hidden"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

                <div class="flex flex-1 items-center gap-2 overflow-hidden">
                    <svg
                        class="hidden h-4 w-4 shrink-0 text-emerald-500 sm:block"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M13 5l7 7-7 7M5 5l7 7-7 7"
                        />
                    </svg>
                    <p class="truncate text-[12px] font-medium text-slate-600">
                        <span class="text-slate-500">Navegando:</span>
                        <span class="ml-1 font-medium text-slate-700">{{
                            originBreadcrumb
                        }}</span>
                    </p>
                </div>
                <div
                    class="ml-3 flex shrink-0 items-center gap-3 border-l border-slate-200 pl-3"
                >
                    <div
                        class="flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-medium text-slate-500"
                    >
                        <svg
                            class="h-3.5 w-3.5 text-emerald-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        {{ horaActual }}
                    </div>
                </div>
            </header>

            <!-- Main body -->
            <main class="flex-1 overflow-x-auto overflow-y-auto p-4 sm:p-5">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import NavGroup from './NavGroup.vue';
import NavItem from './NavItem.vue';
import NavSubItem from './NavSubItem.vue';

const page = usePage();

function getInitialSidebarState() {
    if (typeof window !== 'undefined') {
        const path = window.location.pathname;
        const expandsOnRoutes = [
            '/produccion/peru',
            '/produccion/chile',
            '/produccion/colombia',
            '/produccion/australia',
            '/produccion/hora',
            '/produccion/eficiencia',
            '/produccion/controlar',
            '/maquinas/visibles',
            '/maquinas/no-visibles',
            '/maquinas/baja-fisica',
        ];
        return expandsOnRoutes.some((route) => path.startsWith(route));
    }
    return false;
}

const sidebarOpen = ref(getInitialSidebarState());
const horaActual = ref('');
let timer = null;

const userName = computed(() => page.props.auth?.user?.Nombres || 'Usuario');
const userRole = computed(() => page.props.auth?.user?.rol || 'Personal');
const userInitials = computed(() => {
    const name = userName.value;

    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
});

const isActive = (path) =>
    typeof window !== 'undefined' && window.location.pathname === path;

const expandSidebar = () => {
    sidebarOpen.value = true;
};

const isGroupActive = (groupName) => {
    const path = typeof window !== 'undefined' ? window.location.pathname : '';

    if (groupName === 'produccion') {
        return [
            '/produccion/peru',
            '/produccion/chile',
            '/produccion/colombia',
            '/produccion/australia',
            '/produccion/hora',
            '/produccion/eficiencia',
        ].includes(path);
    }

    if (groupName === 'consultas') {
        return ['/consultas/datos', '/consultas/maquinas'].includes(path);
    }

    if (groupName === 'reportes') {
        return [
            '/reportes/diario',
            '/reportes/historico',
            '/reportes/centros',
            '/reportes/comparativo',
        ].includes(path);
    }

    return false;
};

const originBreadcrumb = computed(() => {
    const path = typeof window !== 'undefined' ? window.location.pathname : '';

    if (path === '/dashboard') {
        return 'Dashboard Principal';
    }

    if (path.startsWith('/produccion/peru')) {
        return 'Producción > Perú';
    }

    if (path.startsWith('/produccion/chile')) {
        return 'Producción > Chile';
    }

    if (path.startsWith('/produccion/colombia')) {
        return 'Producción > Colombia';
    }

    if (path.startsWith('/produccion/australia')) {
        return 'Producción > Australia';
    }

    if (path.startsWith('/produccion/eficiencia')) {
        return 'Producción > Eficiencia';
    }

    if (path.startsWith('/consultas/datos')) {
        return 'Consultas > Datos';
    }

    if (path.startsWith('/consultas/maquinas')) {
        return 'Consultas > Máquinas';
    }

    if (path.startsWith('/reportes/diario')) {
        return 'Reportes > Diario';
    }

    if (path.startsWith('/reportes/historico')) {
        return 'Reportes > Histórico';
    }

    if (path.startsWith('/reportes/centros')) {
        return 'Reportes > Centros';
    }

    if (path.startsWith('/reportes/comparativo')) {
        return 'Reportes > Comparativo';
    }

    return 'Dashboard';
});

const updateClock = () => {
    horaActual.value = new Date().toLocaleTimeString('es-PE', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

onMounted(() => {
    updateClock();
    timer = setInterval(updateClock, 1000);

    if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
    }
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const logout = () => {
    fetch('/logout', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                ?.content,
        },
    }).then(() => {
        window.location.href = '/login';
    });
};

defineExpose({ sidebarOpen });
</script>
