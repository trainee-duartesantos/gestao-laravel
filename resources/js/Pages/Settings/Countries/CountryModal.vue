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

const title = computed(() =>
    props.mode === "edit" ? "Editar país" : "Novo país"
);

const close = () => {
    emit("update:show", false);
};

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
        <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">{{ title }}</h2>

                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600"
                >
                    ✕
                </button>
            </div>

            <!-- Form -->
            <div class="space-y-4">
                <!-- Código -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Código (2 letras)
                    </label>
                    <input
                        type="text"
                        maxlength="2"
                        :value="modelValue.code"
                        @input="
                            updateField(
                                'code',
                                $event.target.value.toUpperCase()
                            )
                        "
                        class="w-full border rounded px-3 py-2"
                    />
                    <p v-if="errors?.code" class="text-sm text-red-600">
                        {{ errors.code[0] }}
                    </p>
                </div>

                <!-- Nome -->
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

            <!-- Footer -->
            <div class="flex justify-end gap-3 mt-6">
                <button class="px-4 py-2 border rounded" @click="close">
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
