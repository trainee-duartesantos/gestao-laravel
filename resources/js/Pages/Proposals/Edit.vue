<script setup>
import { ref } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import ArticleSelector from "@/Components/Proposals/ArticleSelector.vue";

const page = usePage();

// dados já vêm do backend
const proposal = ref(page.props.proposal);
const articles = page.props.articles;

const addLine = async ({ article_id, quantity }) => {
    await axios.post(`/proposals/${proposal.value.id}/lines`, {
        article_id,
        quantity,
    });

    // refresca a página para atualizar linhas + totais
    location.reload();
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
        <ArticleSelector :articles="articles" @add="addLine" />

        <!-- LINHAS -->
        <table class="w-full border mt-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-left">Descrição</th>
                    <th class="p-2 text-right">Qtd</th>
                    <th class="p-2 text-right">Preço</th>
                    <th class="p-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="line in proposal.lines" :key="line.id">
                    <td class="p-2">{{ line.description }}</td>
                    <td class="p-2 text-right">{{ line.quantity }}</td>
                    <td class="p-2 text-right">{{ line.unit_price }} €</td>
                    <td class="p-2 text-right">{{ line.total }} €</td>
                </tr>
            </tbody>
        </table>

        <!-- TOTAIS -->
        <div class="mt-4 text-right">
            <div>Subtotal: {{ proposal.subtotal }} €</div>
            <div>IVA: {{ proposal.vat_total }} €</div>
            <div class="font-bold text-lg">Total: {{ proposal.total }} €</div>
        </div>
    </div>
</template>
