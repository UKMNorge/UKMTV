<script setup>
const placeholder = '/logo.svg';

defineProps({
    film: Object,
});

const onImgError = (event) => {
    event.target.onerror = null;
    event.target.src = placeholder;
    event.target.classList.remove('object-cover');
    event.target.classList.add('object-contain', 'p-8', 'bg-slate-900');
};
</script>

<template>
    <a :href="`/film/${film.id || film.tv_id || film.cf_id}`" class="group cursor-pointer">
        <div class="bg-slate-800 rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition duration-300">
            <!-- Thumbnail (stripped overlays to guarantee visibility) -->
            <div class="relative w-full" style="aspect-ratio: 16/9; min-height: 180px;">
                <img 
                    :src="film.thumbnail_url || placeholder"
                    :alt="film.title"
                    crossorigin="anonymous"
                    loading="lazy"
                    :class="[
                        'absolute inset-0 w-full h-full',
                        film.thumbnail_url ? 'object-cover' : 'object-contain p-8 bg-slate-900'
                    ]"
                    style="display: block !important;"
                    @error="onImgError"
                />

                <!-- Year Badge -->
                <div class="absolute top-2 right-2 bg-purple-600 text-white px-2 py-1 rounded text-xs font-semibold z-10">
                    {{ film.year }}
                </div>
            </div>

            <!-- Info -->
            <div class="p-4">
                <h3 class="text-lg font-semibold text-white group-hover:text-purple-400 transition line-clamp-2 mb-2 text-left">
                    {{ film.title }}
                </h3>

                <div v-if="film.kommune || film.fylke" class="text-xs text-slate-300 mb-2 text-left flex items-center gap-1">
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 2a6 6 0 016 6c0 4.418-6 10-6 10S4 12.418 4 8a6 6 0 016-6zm0 3a3 3 0 100 6 3 3 0 000-6z" clip-rule="evenodd" />
                    </svg>
                    <span class="line-clamp-1">
                        {{ film.kommune || film.fylke }}<span v-if="film.kommune && film.fylke"> · {{ film.fylke }}</span>
                    </span>
                </div>
                
                <div class="flex items-center justify-end text-sm text-slate-400">
                    <div class="flex items-center space-x-1" v-if="film.duration">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 102 0V6z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ Math.floor(film.duration / 60) }}m</span>
                    </div>
                </div>
            </div>
        </div>
    </a>
</template>
