<script setup>
import { ref, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";

// UI
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
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";

/* --------------------------------------------------------------------------
| Roles list
-------------------------------------------------------------------------- */
const roles = ref([]);
const loading = ref(false);

const fetchRoles = async () => {
    loading.value = true;
    try {
        const res = await fetch("/gestao-acessos/roles/list");
        roles.value = await res.json();
    } finally {
        loading.value = false;
    }
};

onMounted(fetchRoles);

/* --------------------------------------------------------------------------
| Create / Edit Role (useForm)
-------------------------------------------------------------------------- */
const showRoleModal = ref(false);
const editingRole = ref(null);

const roleForm = useForm({
    name: "",
});

const openCreate = () => {
    editingRole.value = null;
    roleForm.reset();
    showRoleModal.value = true;
};

const openEdit = (role) => {
    editingRole.value = role;
    roleForm.name = role.name;
    showRoleModal.value = true;
};

const submitRole = () => {
    if (editingRole.value) {
        roleForm.put(`/gestao-acessos/roles/${editingRole.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showRoleModal.value = false;
                editingRole.value = null;
                roleForm.reset();
                fetchRoles();
            },
        });
    } else {
        roleForm.post("/gestao-acessos/roles", {
            preserveScroll: true,
            onSuccess: () => {
                showRoleModal.value = false;
                roleForm.reset();
                fetchRoles();
            },
        });
    }
};

/* --------------------------------------------------------------------------
| Delete Role (useForm)
-------------------------------------------------------------------------- */
const deleteForm = useForm({});

const deleteRole = (role) => {
    if (!confirm(`Apagar o role "${role.name}"?`)) return;

    deleteForm.delete(`/gestao-acessos/roles/${role.id}`, {
        preserveScroll: true,
        onSuccess: () => fetchRoles(),
    });
};

/* --------------------------------------------------------------------------
| Permissions modal (useForm)
-------------------------------------------------------------------------- */
const permissionsModalOpen = ref(false);
const permissionsLoading = ref(false);
const selectedRole = ref(null);

const permissionsData = ref({});

const permissionsForm = useForm({
    permissions: [],
});

const openPermissions = async (role) => {
    selectedRole.value = role;
    permissionsModalOpen.value = true;
    permissionsLoading.value = true;

    try {
        const res = await fetch(`/gestao-acessos/roles/${role.id}/permissions`);
        const data = await res.json();

        permissionsData.value = data.permissions;
        permissionsForm.permissions = [...data.assigned];
    } finally {
        permissionsLoading.value = false;
    }
};

const togglePermission = (perm) => {
    if (permissionsForm.permissions.includes(perm)) {
        permissionsForm.permissions = permissionsForm.permissions.filter(
            (p) => p !== perm
        );
    } else {
        permissionsForm.permissions.push(perm);
    }
};

const isGroupFullySelected = (perms) => {
    return perms.every((p) => permissionsForm.permissions.includes(p));
};

const toggleGroup = (perms) => {
    if (isGroupFullySelected(perms)) {
        permissionsForm.permissions = permissionsForm.permissions.filter(
            (p) => !perms.includes(p)
        );
    } else {
        perms.forEach((p) => {
            if (!permissionsForm.permissions.includes(p)) {
                permissionsForm.permissions.push(p);
            }
        });
    }
};

const savePermissions = () => {
    console.log("selectedRole.value:", selectedRole.value);
    console.log("selectedRole.value.id:", selectedRole.value?.id);
    if (!selectedRole.value?.id) {
        alert("Erro: Role ID não definido!");
        return;
    }   
    permissionsForm.put(`/gestao-acessos/roles/${selectedRole.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            permissionsModalOpen.value = false;
            permissionsForm.reset();
            selectedRole.value = null;
            fetchRoles();
        },
    });
};
</script>

<template>
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Gestão de Acessos · Roles</h1>
            <Button @click="openCreate">Criar Role</Button>
        </div>

        <!-- Roles table -->
        <Card>
            <CardHeader>
                <CardTitle>Roles existentes</CardTitle>
            </CardHeader>

            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
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
                                    size="sm"
                                    variant="outline"
                                    @click="openPermissions(role)"
                                >
                                    Permissões
                                </Button>

                                <Button
                                    size="sm"
                                    variant="secondary"
                                    @click="openEdit(role)"
                                >
                                    Editar
                                </Button>

                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="deleteRole(role)"
                                >
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

        <!-- Create / Edit Role -->
        <Dialog v-model:open="showRoleModal">
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
                            v-model="roleForm.name"
                            placeholder="Ex: Comercial"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showRoleModal = false">
                        Cancelar
                    </Button>
                    <Button :disabled="roleForm.processing" @click="submitRole">
                        {{ editingRole ? "Guardar" : "Criar" }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Permissions Modal -->
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
                        <h3 class="font-semibold mb-2 capitalize">
                            {{ group }}
                        </h3>

                        <div class="flex items-center gap-2 mb-3">
                            <input
                                type="checkbox"
                                :checked="isGroupFullySelected(perms)"
                                @change="toggleGroup(perms)"
                            />
                            <span class="text-sm font-medium">
                                Selecionar tudo
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <label
                                v-for="perm in perms"
                                :key="perm"
                                class="flex items-center gap-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        permissionsForm.permissions.includes(
                                            perm
                                        )
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
                    <Button
                        :disabled="permissionsForm.processing"
                        @click="savePermissions"
                    >
                        Guardar permissões
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
