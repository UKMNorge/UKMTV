<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FilmCard from '@/Components/FilmCard.vue';
import { ref, onMounted, onBeforeUnmount } from 'vue';

defineProps({
    query: String,
    films: Array,
});

const searchQuery = ref('');
const suggestions = ref([]);
const isLoadingSuggestions = ref(false);
let suggestTimer = null;
const searchBox = ref(null);

const handleSearch = () => {
    if (searchQuery.value.length > 1) {
        window.location.href = `/search/results?q=${encodeURIComponent(searchQuery.value)}`;
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
            suggestions.value = Array.isArray(data) ? data.slice(0, 8) : [];
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
    <Head :title="`Søk: ${query} - Videoarkiv`" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800">
        <!-- Header -->
        <div class="bg-slate-800 border-b border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <Link href="/search" class="text-purple-300 hover:text-purple-200 text-sm mb-4 inline-block">
                    ← Tilbake til søk
                </Link>
                <h1 class="text-4xl font-bold text-white">
                    Søk: "{{ query }}"
                </h1>
            </div>
        </div>

        <!-- Search Input -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-2xl mb-8">
                <div ref="searchBox" class="relative">
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
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded font-semibold transition"
                    >
                        Søk
                    </button>

                    <div v-if="suggestions.length > 0" class="absolute mt-2 w-full bg-white rounded shadow-lg z-20 text-left max-h-72 overflow-auto" @click.stop>
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

        <!-- Results -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            <div v-if="films.length === 0" class="text-center py-12">
                <p class="text-slate-400 text-lg">
                    Ingen innslag funnet som matcher "{{ query }}"
                </p>
            </div>
            
            <div v-else>
                <p class="text-slate-300 mb-8">
                    Fant {{ films.length }} innslag
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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
