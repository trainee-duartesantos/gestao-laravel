<script setup>
import { computed } from "vue";

const props = defineProps({
    currentPage: {
        type: Number,
        required: true,
    },
    lastPage: {
        type: Number,
        required: true,
    },
    total: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(["change"]);

const visiblePages = computed(() => {
    const pages = [];
    const delta = 2;

    let start = Math.max(1, props.currentPage - delta);
    let end = Math.min(props.lastPage, props.currentPage + delta);

    if (start > 1) {
        pages.push(1);
        if (start > 2) pages.push("...");
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (end < props.lastPage) {
        if (end < props.lastPage - 1) pages.push("...");
        pages.push(props.lastPage);
    }

    return pages;
});
</script>

<template>
    <div v-if="lastPage > 1" class="flex items-center justify-between mt-6">
        <p class="text-sm text-gray-600">
            Página {{ currentPage }} de {{ lastPage }} · {{ total }} registos
        </p>

        <div class="flex items-center gap-1">
            <!-- Primeira -->
            <button
                class="px-3 py-2 border rounded disabled:opacity-50"
                :disabled="currentPage === 1"
                @click="emit('change', 1)"
            >
                «
            </button>

            <!-- Anterior -->
            <button
                class="px-3 py-2 border rounded disabled:opacity-50"
                :disabled="currentPage === 1"
                @click="emit('change', currentPage - 1)"
            >
                ‹
            </button>

            <!-- Números -->
            <template v-for="(page, i) in visiblePages" :key="i">
                <span v-if="page === '...'" class="px-3 py-2 text-gray-500">
                    …
                </span>

                <button
                    v-else
                    @click="emit('change', page)"
                    class="px-3 py-2 border rounded"
                    :class="
                        page === currentPage
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'bg-white hover:bg-gray-100'
                    "
                >
                    {{ page }}
                </button>
            </template>

            <!-- Seguinte -->
            <button
                class="px-3 py-2 border rounded disabled:opacity-50"
                :disabled="currentPage === lastPage"
                @click="emit('change', currentPage + 1)"
            >
                ›
            </button>

            <!-- Última -->
            <button
                class="px-3 py-2 border rounded disabled:opacity-50"
                :disabled="currentPage === lastPage"
                @click="emit('change', lastPage)"
            >
                »
            </button>
        </div>
    </div>
</template>
