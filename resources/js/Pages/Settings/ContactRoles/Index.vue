<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import ContactRoleModal from "./ContactRoleModal.vue";
import EntitiesPagination from "@/Components/Entities/EntitiesPagination.vue";
import EntitiesPerPage from "@/Components/Entities/EntitiesPerPage.vue";

const roles = ref([]);
const loading = ref(true);
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

const form = ref({ name: "" });

const loadRoles = async (page = 1) => {
    loading.value = true;

    const res = await axios.get("/settings/contact-roles/list", {
        params: {
            page,
            per_page: perPage.value,
            search: search.value,
        },
    });

    roles.value = res.data.data;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
    total.value = res.data.total;
    loading.value = false;
};

watch([search, perPage], () => loadRoles(1));

const openCreate = () => {
    mode.value = "create";
    editingId.value = null;
    form.value = { name: "" };
    errors.value = {};
    showModal.value = true;
};

const openEdit = (r) => {
    mode.value = "edit";
    editingId.value = r.id;
    form.value = { name: r.name };
    errors.value = {};
    showModal.value = true;
};

const saveRole = async () => {
    saving.value = true;

    try {
        if (mode.value === "create") {
            await axios.post("/settings/contact-roles", form.value);
        } else {
            await axios.put(
                `/settings/contact-roles/${editingId.value}`,
                form.value
            );
        }

        showModal.value = false;
        await loadRoles(currentPage.value);
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    } finally {
        saving.value = false;
    }
};

const deleteRole = async (role) => {
    if (!confirm(`Eliminar a função "${role.name}"?`)) return;

    await axios.delete(`/settings/contact-roles/${role.id}`);
    await loadRoles(currentPage.value);
};

onMounted(loadRoles);
</script>

<template>
    <div class="p-6">
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold">Funções de Contacto</h1>

            <button
                class="bg-blue-600 text-white px-4 py-2 rounded"
                @click="openCreate"
            >
                + Nova Função
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
                    <th class="px-3 py-2 text-left">Nome</th>
                    <th class="px-3 py-2 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="r in roles" :key="r.id" class="border-t">
                    <td class="px-3 py-2">{{ r.name }}</td>
                    <td class="px-3 py-2 text-center space-x-3">
                        <button
                            class="text-blue-600 hover:underline"
                            @click="openEdit(r)"
                        >
                            Editar
                        </button>

                        <button
                            class="text-red-600 hover:underline"
                            @click="deleteRole(r)"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <EntitiesPagination
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @change="loadRoles"
        />

        <ContactRoleModal
            :show="showModal"
            :mode="mode"
            v-model="form"
            :errors="errors"
            :saving="saving"
            @update:show="showModal = $event"
            @save="saveRole"
        />
    </div>
</template>
