<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user;
</script>

<template>
    <Head title="Dashboard" />
    <div class="p-8">
        <h1 class="mb-4 text-2xl font-bold">Dashboard</h1>
        <p>Bienvenido {{ user?.Nombres }}</p>

        <div class="mt-8 grid grid-cols-2 gap-4">
            <a
                href="/produccion/peru"
                class="rounded bg-blue-500 p-4 text-white"
                >Producción Perú</a
            >
            <a
                href="/produccion/chile"
                class="rounded bg-blue-500 p-4 text-white"
                >Producción Chile</a
            >
            <a
                href="/produccion/colombia"
                class="rounded bg-blue-500 p-4 text-white"
                >Producción Colombia</a
            >
            <a
                href="/produccion/australia"
                class="rounded bg-blue-500 p-4 text-white"
                >Producción Australia</a
            >
        </div>
    </div>
</template>
