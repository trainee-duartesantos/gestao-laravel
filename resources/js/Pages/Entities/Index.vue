<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

// estado
const entities = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const errors = ref({});

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

        <!-- tabela -->
        <table class="min-w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th>Nº</th>
                    <th>Nome</th>
                    <th>NIF</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="e in entities" :key="e.id">
                    <td>{{ e.number }}</td>
                    <td>{{ e.name }}</td>
                    <td>{{ e.nif_normalized }}</td>
                    <td>{{ e.status }}</td>
                    <td>
                        <button
                            class="text-blue-600 underline"
                            @click="openEditModal(e)"
                        >
                            Editar
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- MODAL -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black/40 flex justify-center items-center"
        >
            <div class="bg-white p-6 rounded w-full max-w-lg">
                <h2 class="text-xl mb-4">
                    {{
                        mode === "create" ? "Nova Entidade" : "Editar Entidade"
                    }}
                </h2>

                <input v-model="form.name" placeholder="Nome" class="input" />
                <p v-if="errors.name" class="error">{{ errors.name[0] }}</p>

                <input v-model="form.nif" placeholder="NIF" class="input" />
                <p v-if="errors.nif" class="error">{{ errors.nif[0] }}</p>

                <input v-model="form.email" placeholder="Email" class="input" />
                <p v-if="errors.email" class="error">{{ errors.email[0] }}</p>

                <div class="flex gap-4 mt-2">
                    <label
                        ><input type="checkbox" v-model="form.is_customer" />
                        Cliente</label
                    >
                    <label
                        ><input type="checkbox" v-model="form.is_supplier" />
                        Fornecedor</label
                    >
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showModal = false">Cancelar</button>
                    <button
                        @click="
                            mode === 'create' ? createEntity() : updateEntity()
                        "
                        :disabled="saving"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        {{ saving ? "A guardar..." : "Guardar" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
