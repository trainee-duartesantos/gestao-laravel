<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";

const clients = ref([]);
const loading = ref(false);
const errors = ref({});

const form = ref({
    entity_id: null,
});

const loadClients = async () => {
    const res = await axios.get("/entities/list", {
        params: { type: "client", per_page: 100 },
    });
    clients.value = res.data.data;
};

const createProposal = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const res = await axios.post("/proposals", form.value);

        // redireciona para edição da proposta
        router.visit(`/propostas/${res.data.id}`);
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    } finally {
        loading.value = false;
    }
};

onMounted(loadClients);
</script>

<template>
    <div class="p-6 max-w-xl">
        <h1 class="text-2xl font-bold mb-6">Nova Proposta</h1>

        <!-- CLIENTE -->
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1"> Cliente </label>

            <select
                v-model="form.entity_id"
                class="border rounded px-3 py-2 w-full"
            >
                <option :value="null">Seleciona um cliente</option>

                <option v-for="c in clients" :key="c.id" :value="c.id">
                    {{ c.name }}
                </option>
            </select>

            <p v-if="errors.entity_id" class="text-sm text-red-600">
                {{ errors.entity_id[0] }}
            </p>
        </div>

        <!-- AÇÕES -->
        <div class="flex gap-3">
            <a href="/propostas" class="px-4 py-2 border rounded"> Cancelar </a>

            <button
                class="px-4 py-2 bg-blue-600 text-white rounded"
                :disabled="loading"
                @click="createProposal"
            >
                {{ loading ? "A criar..." : "Criar Proposta" }}
            </button>
        </div>
    </div>
</template>
