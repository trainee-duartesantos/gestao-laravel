<script setup>
import { ref, onMounted } from "vue";
import { router } from "@inertiajs/vue3";

// Shadcn
import {
    Table,
    TableHeader,
    TableHead,
    TableRow,
    TableBody,
    TableCell,
} from "@/Components/ui/table";
import { Button } from "@/Components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";

import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";

const showModal = ref(false);
const editingRole = ref(null);

const form = ref({
    name: "",
});

const openCreate = () => {
    editingRole.value = null;
    form.value.name = "";
    showModal.value = true;
};

const openEdit = (role) => {
    editingRole.value = role;
    form.value.name = role.name;
    showModal.value = true;
};

const roles = ref([]);
const loading = ref(false);

const fetchRoles = async () => {
    loading.value = true;
    try {
        const response = await fetch(route("roles.list"));
        roles.value = await response.json();
    } finally {
        loading.value = false;
    }
};

const permissionsModalOpen = ref(false);
const permissionsLoading = ref(false);

const permissionsData = ref({});
const assignedPermissions = ref([]);
const selectedRole = ref(null);

const openPermissions = async (role) => {
    selectedRole.value = role;
    permissionsModalOpen.value = true;
    permissionsLoading.value = true;

    try {
        const res = await fetch(route("roles.permissions", role.id));
        const data = await res.json();

        permissionsData.value = data.permissions;
        assignedPermissions.value = [...data.assigned];
    } finally {
        permissionsLoading.value = false;
    }
};

const savePermissions = async () => {
    await router.put(
        route("roles.update", selectedRole.value.id),
        {
            permissions: assignedPermissions.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                permissionsModalOpen.value = false;
            },
        }
    );
};

const togglePermission = (perm) => {
    if (assignedPermissions.value.includes(perm)) {
        assignedPermissions.value = assignedPermissions.value.filter(
            (p) => p !== perm
        );
    } else {
        assignedPermissions.value.push(perm);
    }
};

onMounted(fetchRoles);
</script>

<template>
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Gestão de Acessos · Roles</h1>

            <Button @click="openCreate"> Criar Role </Button>
        </div>

        <!-- Card -->
        <Card>
            <CardHeader>
                <CardTitle>Roles existentes</CardTitle>
            </CardHeader>

            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[80px]">ID</TableHead>
                            <TableHead>Nome</TableHead>
                            <TableHead>Permissões</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow v-if="loading">
                            <TableCell colspan="4" class="text-center py-6">
                                A carregar…
                            </TableCell>
                        </TableRow>

                        <TableRow v-for="role in roles" :key="role.id">
                            <TableCell>{{ role.id }}</TableCell>
                            <TableCell class="font-medium">
                                {{ role.name }}
                            </TableCell>
                            <TableCell>
                                {{ role.permissions.length }}
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openPermissions(role)"
                                >
                                    Permissões
                                </Button>

                                <Button
                                    variant="secondary"
                                    size="sm"
                                    @click="openEdit(role)"
                                >
                                    Editar
                                </Button>

                                <Button variant="destructive" size="sm">
                                    Apagar
                                </Button>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="!loading && roles.length === 0">
                            <TableCell colspan="4" class="text-center py-6">
                                Nenhum role encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingRole ? "Editar Role" : "Criar Role" }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <div class="space-y-2">
                        <Label for="name">Nome</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Ex: Comercial"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showModal = false">
                        Cancelar
                    </Button>

                    <Button>
                        {{ editingRole ? "Guardar" : "Criar" }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <Dialog v-model:open="permissionsModalOpen" :key="selectedRole?.id">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>
                        Permissões — {{ selectedRole?.name }}
                    </DialogTitle>
                </DialogHeader>

                <div v-if="permissionsLoading" class="py-6 text-center">
                    A carregar permissões…
                </div>

                <div v-else class="space-y-6 max-h-[60vh] overflow-y-auto">
                    <div
                        v-for="(perms, group) in permissionsData"
                        :key="group"
                        class="border rounded-lg p-4"
                    >
                        <h3 class="font-semibold mb-3 capitalize">
                            {{ group }}
                        </h3>

                        <div class="grid grid-cols-2 gap-3">
                            <label
                                v-for="perm in perms"
                                :key="perm"
                                class="flex items-center gap-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :value="perm"
                                    :checked="
                                        assignedPermissions.includes(perm)
                                    "
                                    @change="togglePermission(perm)"
                                />

                                {{ perm }}
                            </label>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="permissionsModalOpen = false"
                    >
                        Cancelar
                    </Button>

                    <Button @click="savePermissions">
                        Guardar permissões
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
