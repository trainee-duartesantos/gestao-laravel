<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";

const invoices = ref([]);
const pagination = ref({});

const loadInvoices = async (page = 1) => {
    const res = await axios.get(`/invoices/list?page=${page}`);
    invoices.value = res.data.data;
    pagination.value = res.data.meta;
};

onMounted(loadInvoices);

const goToInvoice = (id) => {
    router.visit(`/faturas/${id}/edit`);
};
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Faturas</h1>

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-left">Nº</th>
                    <th class="p-2 text-left">Cliente</th>
                    <th class="p-2 text-left">Data</th>
                    <th class="p-2 text-right">Total</th>
                    <th class="p-2 text-center">Estado</th>
                    <th class="p-2 text-right">Ações</th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="invoice in invoices"
                    :key="invoice.id"
                    class="border-t"
                >
                    <td class="p-2">{{ invoice.number }}</td>
                    <td class="p-2">{{ invoice.client }}</td>
                    <td class="p-2">{{ invoice.date }}</td>
                    <td class="p-2 text-right">{{ invoice.total }} €</td>

                    <td class="p-2 text-center">
                        <span
                            :class="
                                invoice.status === 'paid'
                                    ? 'text-green-600 font-semibold'
                                    : 'text-orange-600 font-semibold'
                            "
                        >
                            {{ invoice.status === "paid" ? "Paga" : "Emitida" }}
                        </span>
                    </td>

                    <td class="p-2 text-right">
                        <button
                            @click="goToInvoice(invoice.id)"
                            class="text-blue-600 hover:underline"
                        >
                            Ver
                        </button>
                    </td>
                </tr>

                <tr v-if="invoices.length === 0">
                    <td colspan="6" class="p-4 text-center text-gray-500">
                        Sem faturas.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
