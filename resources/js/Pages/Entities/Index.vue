<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import EntityModal from "@/Components/Entities/EntityModal.vue";

// estado
const entities = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const errors = ref({});
const filterStatus = ref("all");

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
const loadEntities = async () => {
    loading.value = true;
    try {
        const response = await axios.get("/entities/list");
        entities.value = response.data.data;
    } finally {
        loading.value = false;
    }
};

const filteredEntities = computed(() => {
    if (filterStatus.value === "all") {
        return entities.value;
    }

    return entities.value.filter((e) => e.status === filterStatus.value);
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

        <div class="flex gap-2 mb-4">
            <!-- Todas -->
            <button
                @click="filterStatus = 'all'"
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
                    {{ totalCount }}
                </span>
            </button>

            <!-- Ativas -->
            <button
                @click="filterStatus = 'active'"
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
                    {{ activeCount }}
                </span>
            </button>

            <!-- Inativas -->
            <button
                @click="filterStatus = 'inactive'"
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
                    {{ inactiveCount }}
                </span>
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
                <tr
                    class="border-t hover:bg-gray-50"
                    v-for="e in filteredEntities"
                    :key="e.id"
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
            </tbody>
        </table>

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
