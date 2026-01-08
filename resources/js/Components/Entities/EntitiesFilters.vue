<script setup>
const props = defineProps({
    filterStatus: {
        type: String,
        required: true,
    },
    search: {
        type: String,
        required: true,
    },
    counts: {
        type: Object,
        required: true,
        // { total, active, inactive }
    },
});

const emit = defineEmits(["update:filterStatus", "update:search"]);
</script>

<template>
    <!-- FILTROS -->
    <div class="flex gap-2 mb-4">
        <!-- Todas -->
        <button
            @click="emit('update:filterStatus', 'all')"
            :class="[
                'flex items-center gap-2 px-4 py-2 rounded border',
                filterStatus === 'all'
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700',
            ]"
        >
            Todas
            <span
                class="text-xs px-2 py-0.5 rounded-full"
                :class="
                    filterStatus === 'all'
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200 text-gray-700'
                "
            >
                {{ counts.total }}
            </span>
        </button>

        <!-- Ativas -->
        <button
            @click="emit('update:filterStatus', 'active')"
            :class="[
                'flex items-center gap-2 px-4 py-2 rounded border',
                filterStatus === 'active'
                    ? 'bg-green-600 text-white'
                    : 'bg-white text-gray-700',
            ]"
        >
            Ativas
            <span
                class="text-xs px-2 py-0.5 rounded-full"
                :class="
                    filterStatus === 'active'
                        ? 'bg-green-500 text-white'
                        : 'bg-green-100 text-green-700'
                "
            >
                {{ counts.active }}
            </span>
        </button>

        <!-- Inativas -->
        <button
            @click="emit('update:filterStatus', 'inactive')"
            :class="[
                'flex items-center gap-2 px-4 py-2 rounded border',
                filterStatus === 'inactive'
                    ? 'bg-gray-600 text-white'
                    : 'bg-white text-gray-700',
            ]"
        >
            Inativas
            <span
                class="text-xs px-2 py-0.5 rounded-full"
                :class="
                    filterStatus === 'inactive'
                        ? 'bg-gray-500 text-white'
                        : 'bg-gray-200 text-gray-700'
                "
            >
                {{ counts.inactive }}
            </span>
        </button>
    </div>

    <!-- PESQUISA -->
    <div class="mb-4">
        <input
            :value="search"
            @input="emit('update:search', $event.target.value)"
            type="text"
            placeholder="Pesquisar por nome ou NIF…"
            class="w-full max-w-md px-4 py-2 border rounded focus:ring focus:ring-blue-200"
        />
    </div>
</template>
