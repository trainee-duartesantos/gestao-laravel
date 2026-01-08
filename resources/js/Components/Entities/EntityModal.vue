<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    show: Boolean,
    mode: String, // create | edit
    modelValue: Object,
    errors: Object,
    saving: Boolean,
});

const emit = defineEmits(["update:show", "save"]);

const close = () => {
    emit("update:show", false);
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
        <div class="bg-white w-full max-w-lg rounded shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">
                {{ mode === "create" ? "Nova Entidade" : "Editar Entidade" }}
            </h2>

            <div class="space-y-4">
                <input
                    v-model="modelValue.name"
                    placeholder="Nome"
                    class="w-full border rounded px-3 py-2"
                />
                <p v-if="errors?.name" class="text-red-600 text-sm">
                    {{ errors.name[0] }}
                </p>

                <input
                    v-model="modelValue.nif"
                    placeholder="NIF"
                    class="w-full border rounded px-3 py-2"
                />
                <p v-if="errors?.nif" class="text-red-600 text-sm">
                    {{ errors.nif[0] }}
                </p>

                <input
                    v-model="modelValue.email"
                    placeholder="Email"
                    class="w-full border rounded px-3 py-2"
                />
                <p v-if="errors?.email" class="text-red-600 text-sm">
                    {{ errors.email[0] }}
                </p>

                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="modelValue.is_customer"
                        />
                        Cliente
                    </label>

                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="modelValue.is_supplier"
                        />
                        Fornecedor
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button @click="close" class="px-4 py-2 border rounded">
                    Cancelar
                </button>

                <button
                    @click="$emit('save')"
                    :disabled="saving"
                    class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50"
                >
                    {{ saving ? "A guardar..." : "Guardar" }}
                </button>
            </div>
        </div>
    </div>
</template>
