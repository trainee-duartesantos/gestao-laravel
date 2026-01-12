<script setup>
import { ref } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import ArticleSelector from "@/Components/Proposals/ArticleSelector.vue";

const page = usePage();

// fonte única de verdade
const proposal = ref(page.props.proposal);

/**
 * Recarregar proposta completa (backend)
 */
const reloadProposal = async () => {
    const { data } = await axios.get(`/proposals/${proposal.value.id}`);

    // 🔥 mantém reatividade
    Object.assign(proposal.value, data);
};

/**
 * Adicionar linha
 */
const addLine = async ({ article_id, quantity }) => {
    await axios.post(`/proposals/${proposal.value.id}/lines`, {
        article_id,
        quantity,
    });

    await reloadProposal();
};

/**
 * Remover linha
 */
const removeLine = async (lineId) => {
    if (!confirm("Remover esta linha da proposta?")) return;

    await axios.delete(`/proposals/lines/${lineId}`);
    await reloadProposal();
};

const debounceTimers = {};

const debouncedUpdateLine = (line) => {
    // limpar debounce anterior dessa linha
    if (debounceTimers[line.id]) {
        clearTimeout(debounceTimers[line.id]);
    }

    debounceTimers[line.id] = setTimeout(async () => {
        await axios.patch(`/proposals/lines/${line.id}`, {
            quantity: line.quantity,
        });

        await reloadProposal();
    }, 500); // 400–600ms é o sweet spot
};

const recalcLineTotal = (line) => {
    const subtotal = line.quantity * line.unit_price;
    const vat = subtotal * (line.vat_rate / 100);
    line.total = +(subtotal + vat).toFixed(2);
};

const closeProposal = async () => {
    if (!confirm("Fechar esta proposta? Não será possível editar depois.")) {
        return;
    }

    await axios.post(`/proposals/${proposal.value.id}/close`);
    await reloadProposal();
};

const convertToInvoice = async () => {
    if (
        !confirm(
            "Converter esta proposta em fatura? Esta ação não pode ser revertida."
        )
    ) {
        return;
    }

    const res = await axios.post(`/proposals/${proposal.value.id}/invoice`);

    // 👉 redirecionar para a fatura
    window.location.href = `/faturas/${res.data.invoice_id}/edit`;
};
</script>

<template>
    <div class="p-6">
        <!-- CABEÇALHO -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold">
                Proposta Nº {{ proposal.number }}
            </h1>

            <p class="text-sm text-gray-600">
                Cliente ID: {{ proposal.entity_id }}
            </p>

            <p class="text-sm text-gray-600">Data: {{ proposal.date }}</p>
            <p class="text-sm text-gray-600">
                Validade: {{ proposal.validity }}
            </p>

            <p class="text-sm">
                Estado:
                <strong>
                    {{ proposal.status === "draft" ? "Rascunho" : "Fechado" }}
                </strong>
            </p>
        </div>

        <!-- ADICIONAR ARTIGO -->
        <ArticleSelector @add="addLine" />

        <!-- LINHAS -->
        <table class="w-full border mt-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-left">Descrição</th>
                    <th class="p-2 text-right">Qtd</th>
                    <th class="p-2 text-right">Preço</th>
                    <th class="p-2 text-right">Total</th>
                    <th class="p-2 text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="line in proposal.lines" :key="line.id">
                    <td class="p-2">{{ line.description }}</td>

                    <td class="p-2 text-right">
                        <input
                            type="number"
                            min="0.01"
                            step="0.01"
                            v-model.number="line.quantity"
                            @input="
                                recalcLineTotal(line);
                                debouncedUpdateLine(line);
                            "
                            class="border rounded px-2 py-1 w-20 text-right"
                            :disabled="proposal.status !== 'draft'"
                        />
                    </td>

                    <td class="p-2 text-right">{{ line.unit_price }} €</td>

                    <td class="p-2 text-right">{{ line.total }} €</td>

                    <td class="p-2 text-right">
                        <button
                            v-if="proposal.status === 'draft'"
                            @click="removeLine(line.id)"
                            class="text-red-600 hover:underline"
                        >
                            Remover
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- TOTAIS -->
        <div class="mt-4 text-right">
            <div>Subtotal: {{ proposal.subtotal }} €</div>
            <div>IVA: {{ proposal.vat_total }} €</div>
            <div class="font-bold text-lg">Total: {{ proposal.total }} €</div>
        </div>
        <button
            v-if="proposal.status === 'draft'"
            @click="closeProposal"
            class="bg-green-600 text-white px-4 py-2 rounded"
        >
            Fechar proposta
        </button>

        <button
            v-if="proposal.status === 'closed'"
            @click="convertToInvoice"
            class="bg-purple-600 text-white px-4 py-2 rounded mt-4"
        >
            Converter em fatura
        </button>
    </div>
</template>
