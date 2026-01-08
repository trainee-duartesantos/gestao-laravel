<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import EntityModal from "@/Components/Entities/EntityModal.vue";
import EntitiesFilters from "@/Components/Entities/EntitiesFilters.vue";
import { watch } from "vue";

// estado
const entities = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const errors = ref({});
const filterStatus = ref("all");
const search = ref("");
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const total = ref(0);

// modo
const mode = ref("create"); // create | edit
const editingId = ref(null);

// formulário
const form = ref({
    name: "",
    nif: "",
    email: "",
    is_customer: true,
    is_supplier: false,
});

// carregar entidades
const loadEntities = async (page = 1) => {
    loading.value = true;

    try {
        const response = await axios.get("/entities/list", {
            params: {
                page,
                per_page: perPage.value,
                status: filterStatus.value,
                search: search.value,
            },
        });

        entities.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        total.value = response.data.total;
    } finally {
        loading.value = false;
    }
};

/*
const filteredEntities = computed(() => {
    return entities.value.filter((e) => {
        // filtro por estado
        const statusMatch =
            filterStatus.value === "all" || e.status === filterStatus.value;

        // pesquisa por nome ou NIF
        const searchValue = search.value.toLowerCase();

        const searchMatch =
            e.name.toLowerCase().includes(searchValue) ||
            e.nif_normalized.includes(searchValue);

        return statusMatch && searchMatch;
    });
});
*/
/*
const paginatedEntities = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredEntities.value.slice(start, start + perPage.value);
});

const totalPages = computed(() =>
    Math.ceil(filteredEntities.value.length / perPage.value)
);
*/
watch([filterStatus, search], () => {
    loadEntities(1);
});

const totalCount = computed(() => entities.value.length);

const activeCount = computed(
    () => entities.value.filter((e) => e.status === "active").length
);

const inactiveCount = computed(
    () => entities.value.filter((e) => e.status === "inactive").length
);

// abrir modal criar
const openCreateModal = () => {
    mode.value = "create";
    editingId.value = null;
    resetForm();
    errors.value = {};
    showModal.value = true;
};

// abrir modal editar
const openEditModal = (entity) => {
    mode.value = "edit";
    editingId.value = entity.id;

    form.value = {
        name: entity.name,
        nif: entity.nif_normalized,
        email: "",
        is_customer: entity.is_customer ?? false,
        is_supplier: entity.is_supplier ?? false,
    };

    errors.value = {};
    showModal.value = true;
};

// criar
const createEntity = async () => {
    saving.value = true;
    try {
        await axios.post("/entities", form.value);
        showModal.value = false;
        resetForm();
        await loadEntities();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        } else {
            alert("Erro inesperado ao criar entidade");
        }
    } finally {
        saving.value = false;
    }
};

// atualizar
const updateEntity = async () => {
    saving.value = true;
    try {
        await axios.put(`/entities/${editingId.value}`, form.value);
        showModal.value = false;
        resetForm();
        await loadEntities();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        } else {
            alert("Erro inesperado ao atualizar entidade");
        }
    } finally {
        saving.value = false;
    }
};

// reset
const resetForm = () => {
    form.value = {
        name: "",
        nif: "",
        email: "",
        is_customer: true,
        is_supplier: false,
    };
};

const toggleStatus = async (entity) => {
    await axios.patch(`/entities/${entity.id}/toggle-status`);
    await loadEntities();
};

const showConfirm = ref(false);
const entityToToggle = ref(null);

const confirmToggle = (entity) => {
    entityToToggle.value = entity;
    showConfirm.value = true;
};

const toggleStatusConfirmed = async () => {
    await axios.patch(`/entities/${entityToToggle.value.id}/toggle-status`);

    showConfirm.value = false;
    entityToToggle.value = null;
    await loadEntities();
};

onMounted(loadEntities);
</script>

<template>
    <div class="p-6">
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold">Entidades</h1>

            <button
                @click="openCreateModal"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                + Nova Entidade
            </button>
        </div>

        <EntitiesFilters
            :filter-status="filterStatus"
            :search="search"
            :counts="{
                total: totalCount,
                active: activeCount,
                inactive: inactiveCount,
            }"
            @update:filterStatus="filterStatus = $event"
            @update:search="search = $event"
        />

        <!-- tabela -->
        <table class="min-w-full border border-gray-200 table-fixed">
            <thead class="bg-gray-100">
                <tr>
                    <th class="w-16 px-3 py-2 text-left">Nº</th>
                    <th class="px-3 py-2 text-left">Nome</th>
                    <th class="w-40 px-3 py-2 text-left">NIF</th>
                    <th class="w-28 px-3 py-2 text-center">Estado</th>
                    <th class="w-40 px-3 py-2 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- SKELETON LOADING -->
                <tr
                    v-if="loading"
                    v-for="n in 5"
                    :key="'skeleton-' + n"
                    class="border-t"
                >
                    <td class="px-3 py-3">
                        <div
                            class="h-4 w-10 bg-gray-200 rounded animate-pulse"
                        ></div>
                    </td>

                    <td class="px-3 py-3">
                        <div
                            class="h-4 w-3/4 bg-gray-200 rounded animate-pulse"
                        ></div>
                    </td>

                    <td class="px-3 py-3">
                        <div
                            class="h-4 w-32 bg-gray-200 rounded animate-pulse"
                        ></div>
                    </td>

                    <td class="px-3 py-3 text-center">
                        <div
                            class="h-5 w-16 mx-auto bg-gray-200 rounded-full animate-pulse"
                        ></div>
                    </td>

                    <td class="px-3 py-3 text-center">
                        <div
                            class="h-4 w-20 mx-auto bg-gray-200 rounded animate-pulse"
                        ></div>
                    </td>
                </tr>

                <tr
                    v-else
                    v-for="e in entities"
                    :key="e.id"
                    class="border-t hover:bg-gray-50"
                >
                    <td class="px-3 py-2">{{ e.number }}</td>
                    <td class="px-3 py-2">{{ e.name }}</td>
                    <td class="px-3 py-2 font-mono text-sm">
                        {{ e.nif_normalized }}
                    </td>
                    <td class="px-3 py-2 text-center">
                        <span
                            :class="[
                                'px-2 py-1 rounded text-xs font-medium',
                                e.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-200 text-gray-600',
                            ]"
                        >
                            {{ e.status }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center space-x-3">
                        <button
                            class="text-blue-600 hover:underline"
                            @click="openEditModal(e)"
                        >
                            Editar
                        </button>

                        <button
                            class="text-sm text-red-600 hover:underline"
                            @click="confirmToggle(e)"
                        >
                            {{ e.status === "active" ? "Desativar" : "Ativar" }}
                        </button>
                    </td>
                </tr>
                <tr v-if="!loading && entities.length === 0">
                    <td colspan="5" class="text-center py-6 text-gray-500">
                        Nenhuma entidade encontrada
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="lastPage > 1" class="flex items-center justify-between mt-6">
            <p class="text-sm text-gray-600">
                Página {{ currentPage }} de {{ lastPage }} ·
                {{ total }} registos
            </p>

            <div class="flex gap-2">
                <button
                    class="px-3 py-2 border rounded disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="loadEntities(currentPage - 1)"
                >
                    Anterior
                </button>

                <button
                    class="px-3 py-2 border rounded disabled:opacity-50"
                    :disabled="currentPage === lastPage"
                    @click="loadEntities(currentPage + 1)"
                >
                    Seguinte
                </button>
            </div>
        </div>

        <div
            v-if="showConfirm"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-3">Confirmar ação</h2>

                <p class="mb-6">
                    Tens a certeza que pretendes
                    <strong>
                        {{
                            entityToToggle?.status === "active"
                                ? "desativar"
                                : "ativar"
                        }}
                    </strong>
                    a entidade
                    <strong>{{ entityToToggle?.name }}</strong
                    >?
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        class="px-4 py-2 border rounded"
                        @click="showConfirm = false"
                    >
                        Cancelar
                    </button>

                    <button
                        class="px-4 py-2 bg-red-600 text-white rounded"
                        @click="toggleStatusConfirmed"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL COMPONENTE -->
        <EntityModal
            :show="showModal"
            :mode="mode"
            v-model="form"
            :errors="errors"
            :saving="saving"
            @update:show="showModal = $event"
            @save="mode === 'create' ? createEntity() : updateEntity()"
        />
    </div>
</template>
