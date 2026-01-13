<script setup>
import { ref } from "vue";
import { useForm, Head } from "@inertiajs/vue3";

import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";

const useRecoveryCode = ref(false);

const form = useForm({
    code: "",
    recovery_code: "",
});

const submit = () => {
    form.post(route("two-factor.login"), {
        onFinish: () => {
            form.reset("code", "recovery_code");
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Verificação em Dois Fatores" />

        <div class="mb-4 text-sm text-gray-600">
            <span v-if="!useRecoveryCode">
                Introduza o código da aplicação de autenticação.
            </span>
            <span v-else> Introduza um dos seus códigos de recuperação. </span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Authenticator code -->
            <div v-if="!useRecoveryCode">
                <InputLabel value="Código de autenticação" />

                <TextInput
                    v-model="form.code"
                    class="mt-1 block w-full"
                    autofocus
                    inputmode="numeric"
                    autocomplete="one-time-code"
                />

                <InputError :message="form.errors.code" />
            </div>

            <!-- Recovery code -->
            <div v-else>
                <InputLabel value="Código de recuperação" />

                <TextInput
                    v-model="form.recovery_code"
                    class="mt-1 block w-full"
                    autocomplete="one-time-code"
                />

                <InputError :message="form.errors.recovery_code" />
            </div>

            <div class="flex items-center justify-between">
                <button
                    type="button"
                    class="text-sm text-gray-600 underline hover:text-gray-900"
                    @click="useRecoveryCode = !useRecoveryCode"
                >
                    <span v-if="!useRecoveryCode">
                        Usar código de recuperação
                    </span>
                    <span v-else> Usar código da aplicação </span>
                </button>

                <PrimaryButton :disabled="form.processing">
                    Confirmar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
