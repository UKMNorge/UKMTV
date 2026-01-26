<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    fylker: Array,
});
</script>

<template>
    <Head title="Fylker - Videoarkiv" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <!-- Header -->
        <div class="bg-slate-800 border-b border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <Link href="/" class="text-purple-300 hover:text-purple-200 text-sm mb-4 inline-block">
                    ← Hjem
                </Link>
                <h1 class="text-4xl font-bold text-white">
                    📍 Fylker
                </h1>
                <p class="text-slate-300 mt-2">
                    Se innslag fra ditt fylke
                </p>
            </div>
        </div>

        <!-- Fylker Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <Link 
                    v-for="fylke in fylker" 
                    :key="fylke.id"
                    :href="`/fylke/${fylke.link}`"
                    class="bg-gradient-to-br from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white p-6 rounded-lg font-semibold transition transform hover:scale-105 shadow-lg hover:shadow-xl"
                >
                    {{ fylke.name }}
                </Link>
            </div>

            <div v-if="fylker.length === 0" class="text-center py-12">
                <p class="text-slate-400 text-lg">
                    Ingen fylker funnet
                </p>
            </div>
        </div>
    </div>
</template>
