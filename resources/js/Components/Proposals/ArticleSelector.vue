<script setup>
import { ref } from "vue";

const props = defineProps({
    articles: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(["add"]);

const selectedArticle = ref("");
const quantity = ref(1);

const addLine = () => {
    if (!selectedArticle.value) return;

    emit("add", {
        article_id: selectedArticle.value,
        quantity: quantity.value,
    });

    selectedArticle.value = "";
    quantity.value = 1;
};
</script>

<template>
    <div class="border p-4 rounded space-y-3">
        <h3 class="font-semibold">Adicionar artigo</h3>

        <select
            v-model="selectedArticle"
            class="border rounded px-2 py-1 w-full"
        >
            <option value="">Selecionar artigo…</option>
            <option v-for="a in articles" :key="a.id" :value="a.id">
                {{ a.name }} — {{ a.price }} €
            </option>
        </select>

        <input
            type="number"
            min="0.01"
            step="0.01"
            v-model="quantity"
            class="border rounded px-2 py-1 w-full"
        />

        <button
            @click="addLine"
            class="bg-blue-600 text-white px-4 py-2 rounded"
        >
            Adicionar linha
        </button>
    </div>
</template>
