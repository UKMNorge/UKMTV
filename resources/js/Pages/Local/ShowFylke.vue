<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    fylke: Object,
    kommuner: Array,
});
</script>

<template>
    <Head :title="`${fylke.name} - Lokalfestivaler`" />
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-white mb-1">{{ fylke.name }}</h1>
                <p class="text-slate-300 text-sm">Lokalfestivaler</p>
            </div>

            <div class="mb-8">
                <h2 class="text-xl text-white font-semibold mb-2">Kommuner med filmer</h2>
                <p class="text-slate-300 text-sm mb-3">Velg kommune for å se filmer og år.</p>
                <div class="flex flex-wrap gap-3">
                    <Link
                        v-for="kommune in kommuner"
                        :key="kommune.id"
                        :href="`/lokal/${fylke.link}/${kommune.defaultYear}/${kommune.id}`"
                        class="relative z-10 inline-flex items-center px-4 py-3 bg-slate-800 hover:bg-purple-700 text-white rounded text-sm shadow transition-colors focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2 focus:ring-offset-slate-900"
                    >
                        {{ kommune.name }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
