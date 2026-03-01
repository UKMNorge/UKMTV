<template>
    <div class="min-h-screen relative">
        <a
            href="https://ukm.no"
            class="fixed left-4 top-4 sm:left-8 sm:top-6 z-50 inline-flex items-center text-slate-100 hover:text-purple-200 transition"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Gå til UKM.no"
        >
            <img src="/logo.svg" alt="UKM" class="h-16 w-16" loading="lazy" />
        </a>
        <slot />
    </div>
</template>
