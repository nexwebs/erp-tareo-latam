<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({ Usu: 'Asistencia', Pass: 'Asistencia' });
const showPassword = ref(false);

const submit = () => form.post('/login');
</script>

<template>
    <div class="flex min-h-screen">
        <div
            class="relative hidden items-center justify-center overflow-hidden bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 md:flex md:flex-1"
        >
            <div
                class="absolute inset-0 opacity-10"
                style="
                    background-image:
                        linear-gradient(
                            rgba(16, 185, 129, 0.3) 1px,
                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(16, 185, 129, 0.3) 1px,
                            transparent 1px
                        );
                    background-size: 48px 48px;
                "
            ></div>

            <div class="relative z-10 max-w-sm px-12">
                <div
                    class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-[0_0_40px_rgba(16,185,129,0.4)]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"
                        />
                    </svg>
                </div>

                <h2
                    class="mb-1 text-3xl font-bold tracking-tight text-slate-100"
                >
                    Sistema de Consolidado
                </h2>
                <p class="mb-10 text-sm text-slate-400">
                    Planet Sistcontrol Latam
                </p>

                <!-- <div class="flex flex-col gap-4">
                    <div v-for="feat in ['Control de asistencia en tiempo real', 'Reportes y exportación avanzada']"
                        :key="feat"
                        class="flex items-center gap-3 text-slate-300 text-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.7)] flex-shrink-0"></span>
                        {{ feat }}
                    </div>
                </div> -->
            </div>
        </div>

        <div
            class="flex flex-1 items-center justify-center bg-slate-900 px-6 py-12 md:max-w-md"
        >
            <div class="w-full max-w-sm">
                <div class="mb-8 flex items-center gap-3 md:hidden">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-sm leading-tight font-semibold text-white"
                        >
                            Sistema de Tareo
                        </p>
                        <p class="text-xs text-slate-500">
                            Planet Sistcontrol Latam
                        </p>
                    </div>
                </div>

                <h1
                    class="mb-1 text-2xl font-bold tracking-tight text-slate-100"
                >
                    Bienvenido
                </h1>
                <p class="mb-8 text-sm text-slate-500">
                    Ingresa tus credenciales para continuar
                </p>

                <form @submit.prevent="submit" class="flex flex-col gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label
                            class="text-xs font-semibold tracking-widest text-slate-400 uppercase"
                            >Usuario</label
                        >
                        <div class="relative flex items-center">
                            <svg
                                class="pointer-events-none absolute left-3.5 h-4 w-4 text-slate-500"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            <input
                                v-model="form.Usu"
                                type="text"
                                placeholder="Ingresa tu usuario"
                                required
                                autocomplete="username"
                                class="w-full rounded-xl border border-slate-700 bg-slate-800 py-3 pr-4 pl-10 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                            />
                        </div>
                        <span
                            v-if="form.errors.Usu"
                            class="text-xs text-red-400"
                            >{{ form.errors.Usu }}</span
                        >
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label
                            class="text-xs font-semibold tracking-widest text-slate-400 uppercase"
                            >Contraseña</label
                        >
                        <div class="relative flex items-center">
                            <svg
                                class="pointer-events-none absolute left-3.5 h-4 w-4 text-slate-500"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                            <input
                                v-model="form.Pass"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Ingresa tu contraseña"
                                required
                                autocomplete="current-password"
                                class="w-full rounded-xl border border-slate-700 bg-slate-800 py-3 pr-11 pl-10 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                            />
                            <button
                                type="button"
                                tabindex="-1"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 p-1 text-slate-500 transition-colors hover:text-slate-300"
                            >
                                <svg
                                    v-if="!showPassword"
                                    class="h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    />
                                </svg>
                            </button>
                        </div>
                        <span
                            v-if="form.errors.Pass"
                            class="text-xs text-red-400"
                            >{{ form.errors.Pass }}</span
                        >
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-1 w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 py-3 text-sm font-semibold tracking-wide text-white transition-all hover:-translate-y-px hover:from-emerald-600 hover:to-teal-700 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="!form.processing">Iniciar Sesión</span>
                        <span
                            v-else
                            class="flex items-center justify-center gap-2"
                        >
                            <svg
                                class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-dasharray="31.4"
                                    stroke-dashoffset="10"
                                />
                            </svg>
                            Ingresando...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
