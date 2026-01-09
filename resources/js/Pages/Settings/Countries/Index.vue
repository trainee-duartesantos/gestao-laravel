<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import CountryModal from "./CountryModal.vue";
import EntitiesPagination from "@/Components/Entities/EntitiesPagination.vue";
import EntitiesPerPage from "@/Components/Entities/EntitiesPerPage.vue";

const countries = ref([]);
const loading = ref(true);
const error = ref(null);

const search = ref("");
const perPage = ref(10);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const showModal = ref(false);
const mode = ref("create");
const editingId = ref(null);
const saving = ref(false);
const errors = ref({});

const form = ref({
    code: "",
    name: "",
});

const loadCountries = async (page = 1) => {
    loading.value = true;
    error.value = null;

    try {
        const res = await axios.get("/settings/countries/list", {
            params: {
                page,
                per_page: perPage.value,
                search: search.value,
            },
        });

        countries.value = res.data.data;
        currentPage.value = res.data.current_page;
        lastPage.value = res.data.last_page;
        total.value = res.data.total;
    } catch {
        error.value = "Erro ao carregar países.";
    } finally {
        loading.value = false;
    }
};

watch([search, perPage], () => loadCountries(1));

const openCreate = () => {
    mode.value = "create";
    editingId.value = null;
    form.value = { code: "", name: "" };
    errors.value = {};
    showModal.value = true;
};

const openEdit = (c) => {
    mode.value = "edit";
    editingId.value = c.id;
    form.value = { code: c.code, name: c.name };
    errors.value = {};
    showModal.value = true;
};

const saveCountry = async () => {
    saving.value = true;

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
        }
    } finally {
        saving.value = false;
    }
};

const deleteCountry = async (country) => {
    if (!confirm(`Eliminar ${country.name}?`)) return;

    await axios.delete(`/settings/countries/${country.id}`);
    await loadCountries(currentPage.value);
};

onMounted(loadCountries);
</script>

<template>
    <div class="p-6">
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold">Países</h1>

            <button
                class="bg-blue-600 text-white px-4 py-2 rounded"
                @click="openCreate"
            >
                + Novo País
            </button>
        </div>

        <div class="flex justify-between mb-3">
            <input
                v-model="search"
                placeholder="Pesquisar..."
                class="border rounded px-3 py-2 w-64"
            />

            <EntitiesPerPage v-model="perPage" />
        </div>

        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Código</th>
                    <th class="px-3 py-2 text-left">Nome</th>
                    <th class="px-3 py-2 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="c in countries" :key="c.id" class="border-t">
                    <td class="px-3 py-2 font-mono">{{ c.code }}</td>
                    <td class="px-3 py-2">{{ c.name }}</td>
                    <td class="px-3 py-2 text-center space-x-3">
                        <button
                            class="text-blue-600 hover:underline"
                            @click="openEdit(c)"
                        >
                            Editar
                        </button>

                        <button
                            class="text-red-600 hover:underline"
                            @click="deleteCountry(c)"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>

                <tr v-if="!loading && countries.length === 0">
                    <td colspan="3" class="text-center py-6 text-gray-500">
                        Nenhum país encontrado
                    </td>
                </tr>
            </tbody>
        </table>

        <EntitiesPagination
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @change="loadCountries"
        />

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
