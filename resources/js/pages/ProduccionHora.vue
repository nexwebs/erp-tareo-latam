<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    datos: any[];
    filtros: { fecha: string; pais: string };
}>();

const horas = Array.from({ length: 16 }, (_, i) => i + 8);

const formatearNumero = (valor: number) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(valor || 0);
};

const totalGeneral = computed(() =>
    props.datos.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0),
);

const totalesHora = computed(() => {
    const result: Record<number, number> = {};
    for (const h of horas) {
        result[h] = props.datos.reduce(
            (sum, item) => sum + (parseFloat(item[`h${h}`]) || 0),
            0,
        );
    }
    return result;
});
</script>

<template>
    <div class="min-h-screen bg-slate-900 px-4 py-6">
        <div class="mx-auto max-w-[1600px]">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Producción por Hora</h1>
                    <p class="text-slate-400">Reporte detallado por hora</p>
                </div>
                <form method="get" class="flex flex-wrap items-center gap-3">
                    <input
                        v-model="filtros.fecha"
                        type="date"
                        name="fecha"
                        class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white"
                    />
                    <select
                        v-model="filtros.pais"
                        name="pais"
                        class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-white"
                    >
                        <option value="peru">Perú</option>
                        <option value="chile">Chile</option>
                        <option value="colombia">Colombia</option>
                        <option value="australia">Australia</option>
                    </select>
                    <button
                        type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700"
                    >
                        Filtrar
                    </button>
                </form>
            </div>

            <div class="mb-4 overflow-x-auto rounded-xl border border-slate-700 bg-slate-800/50">
                <table class="w-full min-w-[1200px]">
                    <thead>
                        <tr class="bg-slate-800/80">
                            <th class="sticky left-0 z-10 border-r border-b border-slate-700 bg-slate-800 px-3 py-2 text-center text-xs font-semibold whitespace-nowrap text-slate-300">
                                ITEM
                            </th>
                            <th class="sticky left-16 z-10 border-r border-b border-slate-700 bg-slate-800 px-3 py-2 text-center text-xs font-semibold whitespace-nowrap text-slate-300">
                                MODELO
                            </th>
                            <th class="sticky left-36 z-10 border-r border-b border-slate-700 bg-slate-800 px-3 py-2 text-center text-xs font-semibold whitespace-nowrap text-slate-300">
                                CENTRO COMERCIAL
                            </th>
                            <th class="sticky left-[260px] z-10 border-r border-b border-slate-700 bg-slate-800 px-3 py-2 text-center text-xs font-semibold whitespace-nowrap text-slate-300">
                                SERIE
                            </th>
                            <th
                                v-for="h in horas"
                                :key="h"
                                class="border-r border-b border-slate-700 px-2 py-2 text-center text-xs font-semibold whitespace-nowrap text-slate-400"
                            >
                                {{ h }}:00
                            </th>
                            <th class="border-r border-b border-slate-700 bg-emerald-900/50 px-3 py-2 text-center text-xs font-bold whitespace-nowrap text-emerald-400">
                                TOTAL
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in datos"
                            :key="row.item"
                            class="border-b border-slate-700/50 hover:bg-slate-700/30"
                        >
                            <td class="sticky left-0 border-r border-slate-700 bg-slate-900 px-3 py-2 text-center text-sm whitespace-nowrap text-white">
                                {{ row.item }}
                            </td>
                            <td class="sticky left-16 border-r border-slate-700 bg-slate-900 px-3 py-2 text-sm whitespace-nowrap text-slate-300">
                                {{ row.modelo }}
                            </td>
                            <td class="sticky left-36 max-w-[200px] truncate border-r border-slate-700 bg-slate-900 px-3 py-2 text-sm whitespace-nowrap text-slate-300">
                                {{ row.centro }}
                            </td>
                            <td class="sticky left-[260px] border-r border-slate-700 bg-slate-900 px-3 py-2 font-mono text-sm whitespace-nowrap text-cyan-400">
                                {{ row.serie }}
                            </td>
                            <td
                                v-for="h in horas"
                                :key="h"
                                class="border-r border-slate-700 px-2 py-2 text-center text-xs whitespace-nowrap"
                                :class="row[`h${h}`] > 0 ? 'text-emerald-400' : 'text-slate-600'"
                            >
                                {{ formatearNumero(row[`h${h}`]) }}
                            </td>
                            <td class="border-r border-slate-700 bg-emerald-900/20 px-3 py-2 text-right text-sm font-bold whitespace-nowrap text-emerald-400">
                                {{ formatearNumero(row.total) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-800">
                            <td colspan="4" class="sticky left-0 border-r border-slate-700 bg-slate-800 px-3 py-3 text-right text-sm font-bold text-white">
                                TOTAL
                            </td>
                            <td
                                v-for="h in horas"
                                :key="h"
                                class="border-r border-slate-700 px-2 py-2 text-center text-xs font-semibold text-emerald-400"
                            >
                                {{ formatearNumero(totalesHora[h]) }}
                            </td>
                            <td class="border-r border-slate-700 bg-emerald-900/50 px-3 py-3 text-right text-sm font-bold text-emerald-400">
                                {{ formatearNumero(totalGeneral) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>