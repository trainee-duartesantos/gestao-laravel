<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

// estado
const entities = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);

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

// criar entidade ✅ AGORA DENTRO DE FUNÇÃO
const createEntity = async () => {
    saving.value = true;
    try {
        await axios.post("/entities", {
            name: form.value.name,
            nif: form.value.nif,
            email: form.value.email,
            is_customer: form.value.is_customer,
            is_supplier: form.value.is_supplier,
        });

        showModal.value = false;
        resetForm();
        await loadEntities();
    } catch (e) {
        if (e.response?.status === 422) {
            alert(Object.values(e.response.data.errors).flat().join("\n"));
        } else {
            alert("Erro inesperado ao criar entidade");
        }
    } finally {
        saving.value = false;
    }
};

// reset formulário
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
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Entidades</h1>

            <button
                @click="showModal = true"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
            >
                + Nova Entidade
            </button>
        </div>

        <!-- tabela -->
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-4 py-2 text-left">Nº</th>
                    <th class="border px-4 py-2 text-left">Nome</th>
                    <th class="border px-4 py-2 text-left">NIF</th>
                    <th class="border px-4 py-2 text-left">Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="entity in entities" :key="entity.id">
                    <td class="border px-4 py-2">{{ entity.number }}</td>
                    <td class="border px-4 py-2">{{ entity.name }}</td>
                    <td class="border px-4 py-2">
                        {{ entity.nif_normalized }}
                    </td>
                    <td class="border px-4 py-2">{{ entity.status }}</td>
                </tr>

                <tr v-if="!loading && entities && entities.length === 0">
                    <td colspan="4" class="text-center py-4 text-gray-500">
                        Nenhuma entidade encontrada
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- MODAL -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        >
            <div class="bg-white w-full max-w-lg rounded shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Nova Entidade</h2>

                <div class="space-y-4">
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Nome"
                        class="w-full border rounded px-3 py-2"
                    />

                    <input
                        v-model="form.nif"
                        type="text"
                        placeholder="NIF"
                        class="w-full border rounded px-3 py-2"
                    />

                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="Email"
                        class="w-full border rounded px-3 py-2"
                    />

                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.is_customer" />
                            Cliente
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.is_supplier" />
                            Fornecedor
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 border rounded"
                    >
                        Cancelar
                    </button>

                    <button
                        @click="createEntity"
                        :disabled="saving"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ saving ? "A guardar..." : "Guardar" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
