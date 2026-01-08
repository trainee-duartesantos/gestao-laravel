<script setup>
const props = defineProps({
    modelValue: {
        type: Number,
        required: true,
    },
    options: {
        type: Array,
        default: () => [10, 25, 50],
    },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="flex items-center gap-2 text-sm">
        <span class="text-gray-600">Mostrar</span>

        <div class="relative">
            <select
                :value="modelValue"
                @change="emit('update:modelValue', Number($event.target.value))"
                class="border rounded px-2 pr-8 py-1 focus:ring focus:ring-blue-200 appearance-none bg-white"
            >
                <option v-for="opt in options" :key="opt" :value="opt">
                    {{ opt }}
                </option>
            </select>
        </div>

        <span class="text-gray-600">por página</span>
    </div>
</template>
