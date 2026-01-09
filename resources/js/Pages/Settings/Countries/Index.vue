<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";

import CountryModal from "@/Components/Settings/CountryModal.vue";
import EntitiesPagination from "@/Components/Entities/EntitiesPagination.vue";
import EntitiesPerPage from "@/Components/Entities/EntitiesPerPage.vue";

/* =========================
   Estado
========================= */
const countries = ref([]);
const loading = ref(true);
const error = ref(null);

const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const search = ref("");
let searchTimeout = null;

const showModal = ref(false);
const mode = ref("create"); // create | edit
const editingId = ref(null);

const saving = ref(false);
const errors = ref({});

/* =========================
   Form
========================= */
const form = ref({
    code: "",
    name: "",
});

/* =========================
   Carregar países
========================= */
const loadCountries = async (page = 1) => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get("/settings/countries/list", {
            params: {
                page,
                per_page: perPage.value,
                search: search.value,
            },
        });

        countries.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        total.value = response.data.total;
    } catch (e) {
        error.value = "Erro ao carregar países.";
    } finally {
        loading.value = false;
    }
};

/* =========================
   Watchers
========================= */
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadCountries(1);
    }, 300);
});

watch(perPage, () => {
    loadCountries(1);
});

/* =========================
   Modal
========================= */
const openCreateModal = () => {
    mode.value = "create";
    editingId.value = null;
    errors.value = {};
    form.value = { code: "", name: "" };
    showModal.value = true;
};

const openEditModal = (country) => {
    mode.value = "edit";
    editingId.value = country.id;
    errors.value = {};
    form.value = {
        code: country.code,
        name: country.name,
    };
    showModal.value = true;
};

/* =========================
   Guardar
========================= */
const saveCountry = async () => {
    saving.value = true;
    errors.value = {};

    try {
        if (mode.value === "create") {
            await axios.post("/settings/countries", form.value);
        } else {
            await axios.put(
                `/settings/countries/${editingId.value}`,
                form.value
            );
        }

        showModal.value = false;
        await loadCountries(currentPage.value);
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        } else {
            alert("Erro inesperado ao guardar país");
        }
    } finally {
        saving.value = false;
    }
};

/* =========================
   Eliminar
========================= */
const showDeleteConfirm = ref(false);
const countryToDelete = ref(null);

const confirmDelete = (country) => {
    countryToDelete.value = country;
    showDeleteConfirm.value = true;
};

const deleteConfirmed = async () => {
    if (!countryToDelete.value) return;

    await axios.delete(`/settings/countries/${countryToDelete.value.id}`);

    showDeleteConfirm.value = false;
    countryToDelete.value = null;

    await loadCountries(currentPage.value);
};

/* =========================
   Lifecycle
========================= */
onMounted(() => {
    loadCountries();
});
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Países</h1>

            <button
                class="bg-blue-600 text-white px-4 py-2 rounded"
                @click="openCreateModal"
            >
                + Novo País
            </button>
        </div>

        <!-- Pesquisa + per page -->
        <div class="flex justify-between items-center mb-4 gap-4">
            <input
                v-model="search"
                type="text"
                placeholder="Pesquisar país..."
                class="border rounded px-3 py-2 w-64"
            />

            <EntitiesPerPage v-model="perPage" />
        </div>

        <!-- Erro -->
        <div
            v-if="error"
            class="mb-4 p-4 border border-red-200 bg-red-50 text-red-700 rounded"
        >
            {{ error }}
        </div>

        <!-- Tabela -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left w-32">Código</th>
                        <th class="px-3 py-2 text-left">Nome</th>
                        <th class="px-3 py-2 text-center w-40">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Loading -->
                    <tr v-if="loading" v-for="n in 5" :key="n">
                        <td colspan="3" class="px-3 py-4">
                            <div
                                class="h-4 bg-gray-200 rounded animate-pulse"
                            ></div>
                        </td>
                    </tr>

                    <!-- Dados -->
                    <tr
                        v-else
                        v-for="c in countries"
                        :key="c.id"
                        class="border-t hover:bg-gray-50"
                    >
                        <td class="px-3 py-2 font-mono">
                            {{ c.code }}
                        </td>
                        <td class="px-3 py-2">
                            {{ c.name }}
                        </td>
                        <td class="px-3 py-2 text-center space-x-3">
                            <button
                                class="text-blue-600 hover:underline"
                                @click="openEditModal(c)"
                            >
                                Editar
                            </button>

                            <button
                                class="text-red-600 hover:underline"
                                @click="confirmDelete(c)"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>

                    <!-- Empty -->
                    <tr v-if="!loading && countries.length === 0">
                        <td colspan="3" class="py-8 text-center text-gray-500">
                            Nenhum país encontrado
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <EntitiesPagination
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @change="loadCountries"
        />

        <!-- Modal eliminar -->
        <div
            v-if="showDeleteConfirm"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-3 text-red-600">
                    Eliminar país
                </h2>

                <p class="mb-6">
                    Tens a certeza que pretendes eliminar
                    <strong>{{ countryToDelete?.name }}</strong
                    >?
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        class="px-4 py-2 border rounded"
                        @click="showDeleteConfirm = false"
                    >
                        Cancelar
                    </button>

                    <button
                        class="px-4 py-2 bg-red-600 text-white rounded"
                        @click="deleteConfirmed"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal criar / editar -->
        <CountryModal
            :show="showModal"
            :mode="mode"
            v-model="form"
            :errors="errors"
            :saving="saving"
            @update:show="showModal = $event"
            @save="saveCountry"
        />
    </div>
</template>
