<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import Layout from '../components/Layout.vue';

const page = usePage();
const user = page.props.auth?.user;
</script>

<template>
    <Head title="Dashboard" />
    <Layout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <img src="/logo.png" alt="Logo" class="h-12 w-auto" />
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                    <p class="text-slate-500">
                        Bienvenido,
                        <span class="font-semibold text-emerald-600">{{
                            user?.Nombres
                        }}</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a
                    href="/produccion/peru"
                    class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-emerald-300 hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"
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
                                    d="M9 20l-5.447-2.889A1 1 0 013 16.382V5a1 1 0 011-1h4a1 1 0 011 1v8a1 1 0 01-.553.894L5 7m0 0V3m5 4h4a1 1 0 001-1V3"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="font-semibold text-slate-800 group-hover:text-emerald-700"
                            >
                                Perú
                            </p>
                            <p class="text-xs text-slate-400">Producción</p>
                        </div>
                    </div>
                </a>

                <a
                    href="/produccion/chile"
                    class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-blue-300 hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600"
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
                                    d="M9 20l-5.447-2.889A1 1 0 013 16.382V5a1 1 0 011-1h4a1 1 0 011 1v8a1 1 0 01-.553.894L5 7m0 0V3m5 4h4a1 1 0 001-1V3"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="font-semibold text-slate-800 group-hover:text-blue-700"
                            >
                                Chile
                            </p>
                            <p class="text-xs text-slate-400">Producción</p>
                        </div>
                    </div>
                </a>

                <a
                    href="/produccion/colombia"
                    class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-amber-300 hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600"
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
                                    d="M9 20l-5.447-2.889A1 1 0 013 16.382V5a1 1 0 011-1h4a1 1 0 011 1v8a1 1 0 01-.553.894L5 7m0 0V3m5 4h4a1 1 0 001-1V3"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="font-semibold text-slate-800 group-hover:text-amber-700"
                            >
                                Colombia
                            </p>
                            <p class="text-xs text-slate-400">Producción</p>
                        </div>
                    </div>
                </a>

                <a
                    href="/produccion/australia"
                    class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-purple-300 hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600"
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
                                    d="M9 20l-5.447-2.889A1 1 0 013 16.382V5a1 1 0 011-1h4a1 1 0 011 1v8a1 1 0 01-.553.894L5 7m0 0V3m5 4h4a1 1 0 001-1V3"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="font-semibold text-slate-800 group-hover:text-purple-700"
                            >
                                Australia
                            </p>
                            <p class="text-xs text-slate-400">Producción</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </Layout>
</template>
