<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ Usu: '', Pass: '' });
const showPassword = ref(false);

const submit = () => form.post('/login');
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900"
    >
        <div class="relative w-full max-w-md px-4">
            <div
                class="rounded-2xl border border-slate-700/50 bg-slate-800/90 p-8 shadow-2xl backdrop-blur-xl"
            >
                <div class="mb-8 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"
                            />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">
                        Sistema de Tareo
                    </h1>
                    <p class="mt-1 text-slate-400">Planet Sistcontrol Latam</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-300"
                            >Usuario</label
                        >
                        <input
                            v-model="form.Usu"
                            type="text"
                            class="w-full rounded-xl border border-slate-600/50 bg-slate-700/50 px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500/50 focus:outline-none"
                            placeholder="Usuario"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-300"
                            >Contraseña</label
                        >
                        <input
                            v-model="form.Pass"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full rounded-xl border border-slate-600/50 bg-slate-700/50 px-4 py-3 text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500/50 focus:outline-none"
                            placeholder="Contraseña"
                            required
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 py-3 font-semibold text-white shadow-lg hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50"
                    >
                        {{
                            form.processing ? 'Ingresando...' : 'Iniciar Sesión'
                        }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
