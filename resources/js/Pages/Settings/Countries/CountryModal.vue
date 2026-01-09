<script setup>
import { computed } from "vue";

const props = defineProps({
    show: Boolean,
    mode: String, // create | edit
    modelValue: Object,
    errors: Object,
    saving: Boolean,
});

const emit = defineEmits(["update:show", "update:modelValue", "save"]);

const isEdit = computed(() => props.mode === "edit");

const updateField = (field, value) => {
    emit("update:modelValue", {
        ...props.modelValue,
        [field]: value,
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
        <div class="bg-white w-full max-w-md rounded shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4">
                {{ isEdit ? "Editar país" : "Novo país" }}
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Código (2 letras)
                    </label>
                    <input
                        type="text"
                        maxlength="2"
                        :value="modelValue.code"
                        @input="updateField('code', $event.target.value)"
                        class="w-full border rounded px-3 py-2 uppercase"
                    />
                    <p v-if="errors?.code" class="text-sm text-red-600">
                        {{ errors.code[0] }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1"> Nome </label>
                    <input
                        type="text"
                        :value="modelValue.name"
                        @input="updateField('name', $event.target.value)"
                        class="w-full border rounded px-3 py-2"
                    />
                    <p v-if="errors?.name" class="text-sm text-red-600">
                        {{ errors.name[0] }}
                    </p>
                </div>
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
                    {{ isEdit ? "Guardar alterações" : "Criar país" }}
                </button>
            </div>
        </div>
    </div>
</template>
