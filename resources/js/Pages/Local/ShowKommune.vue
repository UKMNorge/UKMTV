<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FilmCard from '@/Components/FilmCard.vue';

defineProps({
    fylke: Object,
    kommune: Object,
    films: Array,
    year: Number,
    years: Array,
});
</script>

<template>
    <Head :title="`${kommune.name} - ${year} - Lokalfestivaler`" />
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm text-slate-300 mb-1">Lokalfestivaler</p>
                    <h1 class="text-3xl font-bold text-white">{{ kommune.name }} – {{ year }}</h1>
                    <p class="text-slate-300 text-sm">{{ fylke.name }}</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="`/lokal/${fylke.link}`" class="text-purple-300 hover:text-purple-200 text-sm">← Til fylke</Link>
                    <Link href="/lokal" class="text-slate-300 hover:text-slate-100 text-sm">Lokalfestivaler</Link>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-xl text-white font-semibold mb-2">År</h2>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="y in years"
                        :key="y"
                        :href="`/lokal/${fylke.link}/${y}/${kommune.id}`"
                        class="px-3 py-2 rounded text-sm"
                        :class="y === year ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-200 hover:bg-slate-700'"
                    >
                        {{ y }}
                    </Link>
                </div>
            </div>

            <div>
                <h2 class="text-xl text-white font-semibold mb-3">Filmer</h2>
                <div v-if="!films || films.length === 0" class="text-slate-300">Ingen filmer funnet.</div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <FilmCard
                        v-for="film in films"
                        :key="film.id"
                        :film="film"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
