<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    fylke: Object,
    years: Array,
    kommuner: Array,
});
</script>

<template>
    <Head :title="`${fylke.name} - Videoarkiv`" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <!-- Header -->
        <div class="bg-slate-800 border-b border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <Link href="/fylke" class="text-purple-300 hover:text-purple-200 text-sm mb-4 inline-block">
                    ← Tilbake til fylker
                </Link>
                <h1 class="text-4xl font-bold text-white">
                    {{ fylke.name }}
                </h1>
            </div>
        </div>

        <!-- Navigation -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Years -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-white mb-6">År med innslag</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <Link 
                        v-for="year in years" 
                        :key="year"
                        :href="`/fylke/${fylke.link}/${year}`"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg text-center font-semibold transition"
                    >
                        {{ year }}
                    </Link>
                </div>
            </div>

            <!-- Kommuner -->
            <div v-if="kommuner.length > 0">
                <h2 class="text-2xl font-bold text-white mb-6">Kommuner med innslag</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <Link 
                        v-for="kommune in kommuner" 
                        :key="kommune.id"
                        :href="`/lokal/${fylke.link}/${kommune.defaultYear}/${kommune.id}`"
                        class="bg-slate-700 hover:bg-slate-600 text-white p-4 rounded-lg transition"
                    >
                        {{ kommune.name }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
