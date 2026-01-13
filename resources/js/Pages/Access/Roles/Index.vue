<script setup>
import { ref, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// Shadcn (ajusta caminhos conforme o teu setup)
import { Button } from "@/Components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";

// Estado
const roles = ref([]);
const permissions = ref([]);
const loading = ref(false);

// Fetch roles
const fetchRoles = async () => {
    loading.value = true;

    const res = await fetch(route("roles.list"));
    roles.value = await res.json();

    loading.value = false;
};

// Fetch permissions (para modal)
const fetchPermissions = async () => {
    const res = await fetch(route("roles.permissions"));
    permissions.value = await res.json();
};

onMounted(() => {
    fetchRoles();
    fetchPermissions();
});
</script>

<template>
    <Head title="Gestão de Acessos · Roles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Gestão de Acessos · Roles
                </h2>

                <Button> Criar Role </Button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Roles existentes</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div v-if="loading" class="text-sm text-gray-500">
                            A carregar roles…
                        </div>

                        <div v-else>
                            <!-- 🔜 Aqui entra o DataTable Shadcn -->
                            <pre class="text-xs bg-gray-100 p-4 rounded"
                                >{{ roles }}
                            </pre>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
