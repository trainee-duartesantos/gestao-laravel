<script setup>
import { computed } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    mode: {
        type: String,
        required: true, // create | edit
    },
    modelValue: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:show", "update:modelValue", "save"]);

const isEdit = computed(() => props.mode === "edit");

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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">
                    {{ isEdit ? "Editar Contacto" : "Novo Contacto" }}
                </h2>

                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600"
                >
                    ✕
                </button>
            </div>

            <!-- Form -->
            <div class="space-y-4">
                <!-- Nome -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Nome *
                    </label>
                    <input
                        type="text"
                        :value="modelValue.name"
                        @input="updateField('name', $event.target.value)"
                        class="w-full border rounded px-3 py-2"
                    />
                    <p v-if="errors.name" class="text-sm text-red-600">
                        {{ errors.name[0] }}
                    </p>
                </div>

                <!-- Função -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Função
                    </label>
                    <input
                        type="text"
                        :value="modelValue.role"
                        @input="updateField('role', $event.target.value)"
                        class="w-full border rounded px-3 py-2"
                    />
                    <p v-if="errors.role" class="text-sm text-red-600">
                        {{ errors.role[0] }}
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Email
                    </label>
                    <input
                        type="email"
                        :value="modelValue.email"
                        @input="updateField('email', $event.target.value)"
                        class="w-full border rounded px-3 py-2"
                    />
                    <p v-if="errors.email" class="text-sm text-red-600">
                        {{ errors.email[0] }}
                    </p>
                </div>

                <!-- Telefone -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Telefone
                    </label>
                    <input
                        type="text"
                        :value="modelValue.phone"
                        @input="updateField('phone', $event.target.value)"
                        class="w-full border rounded px-3 py-2"
                    />
                    <p v-if="errors.phone" class="text-sm text-red-600">
                        {{ errors.phone[0] }}
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 mt-6">
                <button
                    class="px-4 py-2 border rounded"
                    @click="close"
                    :disabled="saving"
                >
                    Cancelar
                </button>

                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
                    :disabled="saving"
                    @click="emit('save')"
                >
                    {{ saving ? "A guardar…" : "Guardar" }}
                </button>
            </div>
        </div>
    </div>
</template>
