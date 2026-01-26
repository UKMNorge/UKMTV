<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FilmCard from '@/Components/FilmCard.vue';

defineProps({
    fylke: Object,
    year: Number,
    films: Array,
});
</script>

<template>
    <Head :title="`${fylke.name} ${year} - Videoarkiv`" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <!-- Header -->
        <div class="bg-slate-800 border-b border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <Link :href="`/fylke/${fylke.link}`" class="text-purple-300 hover:text-purple-200 text-sm mb-4 inline-block">
                    ← Tilbake til {{ fylke.name }}
                </Link>
                <h1 class="text-4xl font-bold text-white">
                    {{ fylke.name }} {{ year }}
                </h1>
            </div>
        </div>

        <!-- Films Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div v-if="films.length === 0" class="text-center py-12">
                <p class="text-slate-400 text-lg">
                    Ingen innslag funnet for {{ fylke.name }} i {{ year }}
                </p>
            </div>
            
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <FilmCard 
                    v-for="film in films" 
                    :key="film.id"
                    :film="film"
                />
            </div>
        </div>
    </div>
</template>
