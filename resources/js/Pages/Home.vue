<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import FilmCard from '@/Components/FilmCard.vue';

defineProps({
    latestFilms: Array,
});

const searchQuery = ref('');
const suggestions = ref([]);
const isLoadingSuggestions = ref(false);
let suggestTimer = null;
const searchBox = ref(null);

const handleSearch = () => {
    if (searchQuery.value && searchQuery.value.trim().length > 1) {
        window.location.href = `/search/results?q=${encodeURIComponent(searchQuery.value.trim())}`;
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Enter') {
        handleSearch();
    }
};

const goToFilm = (film) => {
    if (!film) return;
    const id = film.id || film.tv_id || film.cf_id;
    if (id) {
        window.location.href = `/film/${id}`;
    }
};

const closeSuggestions = () => {
    suggestions.value = [];
};

const handleOutsideClick = (e) => {
    if (!searchBox.value) return;
    if (!searchBox.value.contains(e.target)) {
        closeSuggestions();
    }
};

const fetchSuggestions = async () => {
    const q = searchQuery.value.trim();
    if (q.length < 2) {
        suggestions.value = [];
        return;
    }
    isLoadingSuggestions.value = true;
    try {
        const res = await fetch(`/api/search?q=${encodeURIComponent(q)}`);
        if (res.ok) {
            const data = await res.json();
            suggestions.value = Array.isArray(data) ? data.slice(0, 6) : [];
        } else {
            suggestions.value = [];
        }
    } catch (e) {
        suggestions.value = [];
    } finally {
        isLoadingSuggestions.value = false;
    }
};

const handleInput = () => {
    if (suggestTimer) {
        clearTimeout(suggestTimer);
    }
    suggestTimer = setTimeout(fetchSuggestions, 250);
};

onMounted(() => {
    document.addEventListener('click', handleOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleOutsideClick);
    if (suggestTimer) {
        clearTimeout(suggestTimer);
    }
});
</script>

<template>
    <Head title="Videoarkiv - Se innslag fra hele landet" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <!-- Hero Section -->
        <div class="relative pt-20 pb-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl sm:text-6xl font-bold text-white mb-4">
                    Videoarkiv
                </h1>
                <p class="text-xl text-slate-200 mb-6">
                    Her finner du filmer av innslag fra 2024 og fram til i dag.
                </p>
                
                <!-- Search Bar -->
                <div ref="searchBox" class="relative max-w-md mx-auto mb-8">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Søk etter navn på personer, innslag eller låter"
                        class="w-full px-6 py-3 rounded-lg bg-white text-slate-900 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500"
                        @keydown="handleKeydown"
                        @input="handleInput"
                    />
                    <button
                        @click="handleSearch"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded"
                    >
                        Søk
                    </button>

                    <div v-if="suggestions.length > 0" class="absolute mt-2 w-full bg-white rounded shadow-lg z-20 text-left max-h-64 overflow-auto" @click.stop>
                        <div
                            v-for="film in suggestions"
                            :key="film.id"
                            class="px-4 py-3 hover:bg-slate-100 cursor-pointer"
                            @click="goToFilm(film)"
                        >
                            <p class="text-slate-900 font-semibold line-clamp-1">{{ film.title }}</p>
                            <p class="text-slate-500 text-xs">{{ film.year }}</p>
                        </div>
                    </div>
                    <div v-else-if="isLoadingSuggestions" class="absolute mt-2 w-full bg-white rounded shadow-lg z-20 text-left px-4 py-3 text-slate-500 text-sm">
                        Søker...
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Section -->
        <div class="bg-slate-800 border-t border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-center">
                    <a href="/festival" class="min-w-[180px] bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        🎪 UKM-festivalen
                    </a>
                    <a href="/fylke" class="min-w-[180px] bg-amber-600 hover:bg-amber-700 text-slate-900 px-6 py-3 rounded-lg font-semibold transition">
                        📍 Fylkesfestivaler
                    </a>
                    <a href="/lokal" class="min-w-[180px] bg-purple-700 hover:bg-purple-800 text-white px-6 py-3 rounded-lg font-semibold transition">
                        🏘 Lokalfestivaler
                    </a>
                </div>
            </div>
        </div>

        <!-- Latest Films Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h2 class="text-3xl font-bold text-white mb-8 text-center">
                Siste innslag
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <FilmCard 
                    v-for="film in latestFilms" 
                    :key="film.id"
                    :film="film"
                />
            </div>
        </div>
    </div>
</template>
