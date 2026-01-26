<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    film: Object,
});
</script>

<template>
    <Head :title="`${film.title} - Videoarkiv`" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <!-- Header -->
        <div class="bg-slate-800 border-b border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <Link href="/" class="text-purple-300 hover:text-purple-200 text-sm mb-4 inline-block">
                    ← Hjem
                </Link>
            </div>
        </div>

        <!-- Video Player and Details -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 gap-8">
                <!-- Video -->
                <div>
                    <div class="relative bg-slate-800 rounded-lg overflow-hidden aspect-video mb-8">
                        <iframe
                            v-if="film.video_url"
                            :src="film.video_url"
                            class="w-full h-full"
                            frameborder="0"
                            allowfullscreen
                            allow="autoplay"
                        ></iframe>
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                </svg>
                                <p class="text-slate-400">Video ikke tilgjengelig</p>
                            </div>
                        </div>
                    </div>

                    <!-- Title and Info -->
                    <div class="bg-slate-800 rounded-lg p-6 mb-8">
                        <h1 class="text-3xl font-bold text-white mb-4">
                            {{ film.title }}
                        </h1>

                        <div class="flex flex-wrap gap-4 text-slate-300 mb-6">
                            <div class="flex items-center space-x-2">
                                <span class="text-slate-400">År:</span>
                                <span class="font-semibold">{{ film.year }}</span>
                            </div>
                            <div v-if="film.kommune || film.fylke" class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 2a6 6 0 016 6c0 4.418-6 10-6 10S4 12.418 4 8a6 6 0 016-6zm0 3a3 3 0 100 6 3 3 0 000-6z" clip-rule="evenodd" />
                                </svg>
                                <span class="font-semibold">{{ film.kommune || film.fylke }}</span>
                                <span v-if="film.kommune && film.fylke" class="text-slate-400">/ {{ film.fylke }}</span>
                            </div>
                            <div v-if="film.duration" class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 102 0V6z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ Math.floor(film.duration / 60) }}m {{ film.duration % 60 }}s</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="film.description" class="prose prose-invert max-w-none">
                            <p class="text-slate-300 whitespace-pre-wrap">
                                {{ film.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
