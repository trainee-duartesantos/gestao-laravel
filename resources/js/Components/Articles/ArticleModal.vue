<script setup>
import { computed } from "vue";

const props = defineProps({
    show: Boolean,
    mode: String, // create | edit
    modelValue: Object,
    errors: Object,
    saving: Boolean,
    vatRates: Array,
});

const emit = defineEmits(["update:show", "update:modelValue", "save"]);

const form = computed({
    get: () => props.modelValue,
    set: (val) => emit("update:modelValue", val),
});

const close = () => {
    emit("update:show", false);
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded shadow-lg w-full max-w-lg p-6">
            <h2 class="text-lg font-semibold mb-4">
                {{ mode === "create" ? "Novo Artigo" : "Editar Artigo" }}
            </h2>

            <div class="space-y-4">
                <!-- Código -->
                <div>
                    <label class="block text-sm font-medium">Código</label>
                    <input
                        v-model="form.code"
                        type="text"
                        class="border rounded w-full px-3 py-2"
                    />
                    <p v-if="errors.code" class="text-sm text-red-600">
                        {{ errors.code[0] }}
                    </p>
                </div>

                <!-- Nome -->
                <div>
                    <label class="block text-sm font-medium">Nome</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="border rounded w-full px-3 py-2"
                    />
                    <p v-if="errors.name" class="text-sm text-red-600">
                        {{ errors.name[0] }}
                    </p>
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-sm font-medium">Tipo</label>
                    <select
                        v-model="form.type"
                        class="border rounded w-full px-3 py-2"
                    >
                        <option value="product">Produto</option>
                        <option value="service">Serviço</option>
                    </select>
                </div>

                <!-- Preço -->
                <div>
                    <label class="block text-sm font-medium">Preço (€)</label>
                    <input
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        class="border rounded w-full px-3 py-2"
                    />
                    <p v-if="errors.price" class="text-sm text-red-600">
                        {{ errors.price[0] }}
                    </p>
                </div>

                <!-- IVA -->
                <div>
                    <label class="block text-sm font-medium">IVA</label>
                    <select
                        v-model="form.vat_rate_id"
                        class="border rounded w-full px-3 py-2"
                    >
                        <option
                            v-for="vat in vatRates"
                            :key="vat.id"
                            :value="vat.id"
                        >
                            {{ vat.name }} ({{ vat.rate }}%)
                        </option>
                    </select>
                    <p v-if="errors.vat_rate_id" class="text-sm text-red-600">
                        {{ errors.vat_rate_id[0] }}
                    </p>
                </div>
            </div>

            <!-- AÇÕES -->
            <div class="flex justify-end gap-3 mt-6">
                <button
                    class="px-4 py-2 border rounded"
                    @click="close"
                >
                    Cancelar
                </button>

                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded"
                    :disabled="saving"
                    @click="$emit('save')"
                >
                    {{ saving ? "A guardar..." : "Guardar" }}
                </button>
            </div>
        </div>
    </div>
</template>
