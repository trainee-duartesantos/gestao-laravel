<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const entities = ref([]);
const loading = ref(true);

const loadEntities = async () => {
    try {
        const response = await axios.get("/api/entities");
        entities.value = response.data.data;
    } catch (error) {
        console.error("Erro ao carregar entidades", error);
    } finally {
        loading.value = false;
    }
};

onMounted(loadEntities);
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Entidades</h1>

        <div v-if="loading" class="text-gray-500">A carregar entidades...</div>

        <table v-else class="min-w-full border border-gray-200">
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

                <tr v-if="entities.length === 0">
                    <td colspan="4" class="text-center py-4 text-gray-500">
                        Nenhuma entidade encontrada
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
