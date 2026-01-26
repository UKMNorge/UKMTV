<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    fylker: Array,
});
</script>

<template>
    <Head title="Lokalfestivaler - Videoarkiv" />
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <div class="pt-20 pb-8 px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4">Lokalfestivaler</h1>
            <p class="text-slate-200 max-w-2xl mx-auto">
                Innslag fra kommuner (2023 og nyere)
            </p>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 space-y-10">
            <div>
                <h2 class="text-2xl font-semibold text-white mb-4">Velg fylke</h2>
                <div v-if="!fylker || fylker.length === 0" class="text-slate-300">Ingen fylker med lokalfestival-filmer.</div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <Link
                        v-for="fylke in fylker"
                        :key="fylke.id"
                        :href="`/lokal/${fylke.link}`"
                        class="px-4 py-3 bg-slate-800 hover:bg-slate-700 rounded text-white text-left"
                    >
                        {{ fylke.name }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
