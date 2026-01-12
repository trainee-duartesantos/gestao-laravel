<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const proposals = ref([]);
const loading = ref(true);

const load = async () => {
    loading.value = true;
    const res = await axios.get("/proposals/list");
    proposals.value = res.data.data;
    loading.value = false;
};

onMounted(load);
</script>

<template>
    <div class="p-6">
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold">Propostas</h1>

            <a
                href="/propostas/create"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                + Nova Proposta
            </a>
        </div>

        <table class="min-w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2">Data</th>
                    <th class="px-3 py-2">Número</th>
                    <th class="px-3 py-2">Cliente</th>
                    <th class="px-3 py-2">Validade</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2 text-center">Estado</th>
                </tr>
            </thead>

            <tbody>
                <tr v-if="loading">
                    <td colspan="6" class="py-6 text-center">A carregar...</td>
                </tr>

                <tr v-for="p in proposals" :key="p.id" class="border-t">
                    <td class="px-3 py-2">{{ p.date }}</td>
                    <td class="px-3 py-2 font-mono">{{ p.number }}</td>
                    <td class="px-3 py-2">{{ p.client }}</td>
                    <td class="px-3 py-2">{{ p.validity }}</td>
                    <td class="px-3 py-2 text-right">{{ p.total }} €</td>
                    <td class="px-3 py-2 text-center">
                        {{ p.status === "draft" ? "Rascunho" : "Fechado" }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
