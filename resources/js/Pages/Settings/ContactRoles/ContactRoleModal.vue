<script setup>
import { computed } from "vue";

const props = defineProps({
    show: Boolean,
    mode: String,
    modelValue: Object,
    errors: Object,
    saving: Boolean,
});

const emit = defineEmits(["update:show", "update:modelValue", "save"]);

const isEdit = computed(() => props.mode === "edit");

const updateField = (value) => {
    emit("update:modelValue", { name: value });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
        <div class="bg-white w-full max-w-md rounded shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4">
                {{ isEdit ? "Editar função" : "Nova função" }}
            </h2>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Nome da função
                </label>
                <input
                    type="text"
                    :value="modelValue.name"
                    @input="updateField($event.target.value)"
                    class="w-full border rounded px-3 py-2"
                />
                <p v-if="errors?.name" class="text-sm text-red-600">
                    {{ errors.name[0] }}
                </p>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button
                    class="px-4 py-2 border rounded"
                    @click="$emit('update:show', false)"
                >
                    Cancelar
                </button>

                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded"
                    :disabled="saving"
                    @click="$emit('save')"
                >
                    {{ isEdit ? "Guardar" : "Criar" }}
                </button>
            </div>
        </div>
    </div>
</template>
