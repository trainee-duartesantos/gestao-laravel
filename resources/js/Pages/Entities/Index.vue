<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import EntityModal from "@/Components/Entities/EntityModal.vue";
import EntitiesFilters from "@/Components/Entities/EntitiesFilters.vue";
import EntitiesPagination from "@/Components/Entities/EntitiesPagination.vue";
import EntitiesPerPage from "@/Components/Entities/EntitiesPerPage.vue";

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
const pagesCache = ref({});

let searchTimeout = null;

// modo
const mode = ref("create"); // create | edit
const editingId = ref(null);

const error = ref(null); // mensagem de erro
const hasLoaded = ref(false); // para saber se já tentou carregar

// formulário
const form = ref({
    name: "",
    nif: "",
    email: "",
    is_customer: true,
    is_supplier: false,
});

const makeCacheKey = (page) => {
    return `page=${page}|perPage=${perPage.value}|status=${filterStatus.value}|search=${search.value}`;
};

const params = new URLSearchParams(window.location.search);
perPage.value = Number(params.get("per_page")) || 10;
filterStatus.value = params.get("status") || "all";
search.value = params.get("search") || "";
currentPage.value = Number(params.get("page")) || 1;

const syncUrl = () => {
    const params = new URLSearchParams();

    if (currentPage.value > 1) {
        params.set("page", currentPage.value);
    }

    if (filterStatus.value !== "all") {
        params.set("status", filterStatus.value);
    }

    if (search.value) {
        params.set("search", search.value);
    }

    if (perPage.value !== 10) {
        params.set("per_page", perPage.value);
    }

    const query = params.toString();
    const url = query ? `?${query}` : window.location.pathname;

    window.history.replaceState({}, "", url);
};

// carregar entidades
const loadEntities = async (page = 1) => {
    const cacheKey = makeCacheKey(page);

    error.value = null;

    // 🧠 1️⃣ Se existir cache → usar
    if (pagesCache.value[cacheKey]) {
        const cached = pagesCache.value[cacheKey];
        entities.value = cached.data;
        currentPage.value = cached.currentPage;
        lastPage.value = cached.lastPage;
        total.value = cached.total;
        syncUrl();
        return;
    }

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

        // 🧠 3️⃣ Guardar na cache
        pagesCache.value[cacheKey] = {
            data: response.data.data,
            currentPage: response.data.current_page,
            lastPage: response.data.last_page,
            total: response.data.total,
        };

        syncUrl();
    } catch (e) {
        error.value =
            e.response?.status === 500
                ? "Erro interno do servidor."
                : "Não foi possível carregar as entidades. Verifica a ligação.";
    } finally {
        loading.value = false;
        hasLoaded.value = true;
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
};

watch(search, () => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        pagesCache.value = {}; // 🔑 limpa cache
        loadEntities(1);
    }, 300);
});

watch(filterStatus, () => {
    pagesCache.value = {};
    loadEntities(1);
});

watch(perPage, () => {
    pagesCache.value = {};
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

    pagesCache.value = {};
    showConfirm.value = false;
    entityToToggle.value = null;
    await loadEntities();
};

const visiblePages = computed(() => {
    const pages = [];
    const delta = 2; // quantas páginas antes/depois da atual

    let start = Math.max(1, currentPage.value - delta);
    let end = Math.min(lastPage.value, currentPage.value + delta);

    // garantir sempre mostrar a primeira página
    if (start > 1) {
        pages.push(1);
        if (start > 2) {
            pages.push("...");
        }
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    // garantir sempre mostrar a última página
    if (end < lastPage.value) {
        if (end < lastPage.value - 1) {
            pages.push("...");
        }
        pages.push(lastPage.value);
    }

    return pages;
});

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

        <div class="flex justify-between items-center mb-3">
            <EntitiesPerPage v-model="perPage" />

            <p class="text-sm text-gray-500">{{ total }} registos</p>
        </div>

        <div
            v-if="error"
            class="mb-4 rounded border border-red-200 bg-red-50 p-4 text-red-700"
        >
            <p class="font-medium">Ocorreu um erro</p>
            <p class="text-sm mb-3">{{ error }}</p>

            <button
                class="px-4 py-2 bg-red-600 text-white rounded"
                @click="loadEntities(currentPage)"
            >
                Tentar novamente
            </button>
        </div>

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
                <tr
                    v-if="
                        !loading && hasLoaded && entities.length === 0 && !error
                    "
                >
                    <td colspan="5" class="py-10 text-center text-gray-500">
                        <p class="font-medium">Nenhuma entidade encontrada</p>
                        <p class="text-sm mt-1">
                            Ajusta os filtros ou tenta outra pesquisa.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Paginação -->
        <EntitiesPagination
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @change="loadEntities"
        />

        <!-- Modal confirmação ação -->
        <div
            v-if="showConfirm"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-3">Confirmação</h2>

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
