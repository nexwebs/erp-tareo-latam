<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({ Usu: 'Asistencia', Pass: 'Asistencia' });
const showPassword = ref(true);

const submit = () => form.post('/login');
</script>

<template>
    <div class="flex min-h-screen">
        <div class="hidden md:flex md:flex-1 relative bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 items-center justify-center overflow-hidden">
            <div class="absolute inset-0 opacity-10"
                style="background-image: linear-gradient(rgba(16,185,129,0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(16,185,129,0.3) 1px, transparent 1px); background-size: 48px 48px;">
            </div>

            <div class="relative z-10 px-12 max-w-sm">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mb-6 shadow-[0_0_40px_rgba(16,185,129,0.4)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>

                <h2 class="text-3xl font-bold text-slate-100 tracking-tight mb-1">Sistema de Consolidado</h2>
                <p class="text-slate-400 text-sm mb-10">Planet Sistcontrol Latam</p>

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

        <div class="flex flex-1 md:max-w-md items-center justify-center bg-slate-900 px-6 py-12">
            <div class="w-full max-w-sm">
                <div class="md:hidden flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm leading-tight">Sistema de Tareo</p>
                        <p class="text-slate-500 text-xs">Planet Sistcontrol Latam</p>
                    </div>
                </div>

                <h1 class="text-2xl font-bold text-slate-100 tracking-tight mb-1">Bienvenido</h1>
                <p class="text-slate-500 text-sm mb-8">Ingresa tus credenciales para continuar</p>

                <form @submit.prevent="submit" class="flex flex-col gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Usuario</label>
                        <div class="relative flex items-center">
                            <svg class="absolute left-3.5 w-4 h-4 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <input
                                v-model="form.Usu"
                                type="text"
                                placeholder="Ingresa tu usuario"
                                required
                                autocomplete="username"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-slate-100 text-sm placeholder-slate-600 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                            />
                        </div>
                        <span v-if="form.errors.Usu" class="text-red-400 text-xs">{{ form.errors.Usu }}</span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Contraseña</label>
                        <div class="relative flex items-center">
                            <svg class="absolute left-3.5 w-4 h-4 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input
                                v-model="form.Pass"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Ingresa tu contraseña"
                                required
                                autocomplete="current-password"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-10 pr-11 py-3 text-slate-100 text-sm placeholder-slate-600 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                            />
                            <button
                                type="button"
                                tabindex="-1"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 text-slate-500 hover:text-slate-300 transition-colors p-1"
                            >
                                <svg v-if="!showPassword" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <span v-if="form.errors.Pass" class="text-red-400 text-xs">{{ form.errors.Pass }}</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-1 w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl py-3 text-white text-sm font-semibold tracking-wide transition-all hover:-translate-y-px active:translate-y-0"
                    >
                        <span v-if="!form.processing">Iniciar Sesión</span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-dashoffset="10"/>
                            </svg>
                            Ingresando...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>