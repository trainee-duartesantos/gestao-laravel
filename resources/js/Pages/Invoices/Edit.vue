<script setup>
import { ref } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const invoice = ref(page.props.invoice);

const markAsPaid = async () => {
    if (!confirm("Marcar esta fatura como paga?")) return;

    await axios.post(`/invoices/${invoice.value.id}/mark-paid`);

    // atualizar estado local
    invoice.value.status = "paid";
};

const sendInvoiceByEmail = async () => {
    try {
        await axios.post(`/invoices/${invoice.value.id}/send-email`);
        alert("Email enviado com sucesso");
    } catch (e) {
        alert(e.response?.data?.message ?? "Erro ao enviar email");
    }
};
</script>

<template>
    <div class="p-6">
        <!-- CABEÇALHO -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Fatura Nº {{ invoice.number }}</h1>

            <p class="text-sm text-gray-600">Data: {{ invoice.date }}</p>

            <p class="text-sm">
                Estado:
                <strong
                    :class="
                        invoice.status === 'paid'
                            ? 'text-green-600'
                            : 'text-orange-600'
                    "
                >
                    {{ invoice.status === "paid" ? "Paga" : "Emitida" }}
                </strong>
            </p>
        </div>

        <div class="flex justify-end mb-4">
            <a
                :href="`/faturas/${invoice.id}/pdf`"
                target="_blank"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Download PDF
            </a>
        </div>
        <!-- LINHAS -->
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-left">Descrição</th>
                    <th class="p-2 text-right">Qtd</th>
                    <th class="p-2 text-right">Preço</th>
                    <th class="p-2 text-right">Total</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="line in invoice.lines" :key="line.id">
                    <td class="p-2">{{ line.description }}</td>
                    <td class="p-2 text-right">{{ line.quantity }}</td>
                    <td class="p-2 text-right">{{ line.unit_price }} €</td>
                    <td class="p-2 text-right">{{ line.total }} €</td>
                </tr>
            </tbody>
        </table>

        <!-- TOTAIS -->
        <div class="mt-6 text-right">
            <div>Subtotal: {{ invoice.subtotal }} €</div>
            <div>IVA: {{ invoice.vat_total }} €</div>
            <div class="font-bold text-lg">Total: {{ invoice.total }} €</div>
        </div>

        <!-- AÇÕES -->
        <div class="mt-6 flex gap-3">
            <button
                @click="sendInvoiceByEmail"
                class="bg-red-600 text-white px-4 py-2 rounded"
            >
                Enviar por email
            </button>

            <button
                v-if="invoice.status === 'issued'"
                @click="markAsPaid"
                class="bg-green-600 text-white px-4 py-2 rounded"
            >
                Marcar como paga
            </button>
        </div>
    </div>
</template>
