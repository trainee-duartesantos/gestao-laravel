<script setup>
import { ref, onMounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from "axios";

/* --------------------------------------------------------------------------
| UI (Shadcn)
-------------------------------------------------------------------------- */
import {
    Table,
    TableHeader,
    TableHead,
    TableRow,
    TableBody,
    TableCell,
} from "@/Components/ui/table";

import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";

import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogAction,
    AlertDialogCancel,
} from "@/Components/ui/alert-dialog";

import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Switch } from "@/Components/ui/switch";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

/* --------------------------------------------------------------------------
| Permissions
-------------------------------------------------------------------------- */
import { usePermissions } from "@/composables/usePermissions";
const { can, hasRole } = usePermissions();

/* --------------------------------------------------------------------------
| State
-------------------------------------------------------------------------- */
const users = ref([]);
const roles = ref([]);

const open = ref(false);
const editingUser = ref(null);

const deleteDialogOpen = ref(false);
const userToDelete = ref(null);

/* --------------------------------------------------------------------------
| Form
-------------------------------------------------------------------------- */
const form = useForm({
    name: "",
    email: "",
    phone: "",
    role_id: null,
    is_active: true,
});

/* --------------------------------------------------------------------------
| Data loading
-------------------------------------------------------------------------- */
const loadUsers = async () => {
    users.value = (await axios.get(route("users.list"))).data;
};

const loadRoles = async () => {
    roles.value = (await axios.get("/gestao-acessos/roles/list"))?.data ?? [];
};

onMounted(() => {
    loadUsers();
    loadRoles();
});

/* --------------------------------------------------------------------------
| Create / Edit
-------------------------------------------------------------------------- */
const openCreate = () => {
    editingUser.value = null;
    form.reset();
    form.is_active = true;
    open.value = true;
};

const openEdit = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.phone = user.phone;
    form.role_id = user.role_id;
    form.is_active = user.is_active;
    open.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.put(route("users.update", editingUser.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                loadUsers();
            },
        });
    } else {
        form.post(route("users.store"), {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                loadUsers();
            },
        });
    }
};

/* --------------------------------------------------------------------------
| Delete (AlertDialog)
-------------------------------------------------------------------------- */
const confirmDelete = (user) => {
    userToDelete.value = user;
    deleteDialogOpen.value = true;
};

const destroyUser = () => {
    if (!userToDelete.value) return;

    router.delete(route("users.destroy", userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialogOpen.value = false;
            userToDelete.value = null;
            loadUsers();
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Utilizadores</h1>

            <Button v-if="can('users.create')" @click="openCreate">
                Criar Utilizador
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Utilizadores existentes</CardTitle>
            </CardHeader>

            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Telemóvel</TableHead>
                            <TableHead>Grupo</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow v-if="users.length === 0">
                            <TableCell colspan="6" class="text-center py-6">
                                Nenhum utilizador encontrado.
                            </TableCell>
                        </TableRow>

                        <TableRow v-for="u in users" :key="u.id">
                            <TableCell class="font-medium">
                                {{ u.name }}
                            </TableCell>

                            <TableCell>{{ u.email }}</TableCell>

                            <TableCell>{{ u.phone ?? "-" }}</TableCell>

                            <TableCell>{{ u.role }}</TableCell>

                            <TableCell>
                                <span
                                    :class="
                                        u.is_active
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ u.is_active ? "Ativo" : "Inativo" }}
                                </span>
                            </TableCell>

                            <TableCell class="text-right space-x-2">
                                <Button
                                    v-if="
                                        can('users.edit') &&
                                        (!u.role ||
                                            u.role !== 'Admin' ||
                                            hasRole('Admin'))
                                    "
                                    size="sm"
                                    variant="outline"
                                    @click="openEdit(u)"
                                >
                                    Editar
                                </Button>

                                <Button
                                    v-if="
                                        can('users.delete') &&
                                        u.id !== $page.props.auth.user.id &&
                                        u.role !== 'Admin'
                                    "
                                    size="sm"
                                    variant="destructive"
                                    @click="confirmDelete(u)"
                                >
                                    Apagar
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- MODAL -->
        <Dialog v-model:open="open">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{
                            editingUser
                                ? "Editar Utilizador"
                                : "Novo Utilizador"
                        }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                    <div>
                        <Label>Nome</Label>
                        <Input v-model="form.name" />
                    </div>

                    <div>
                        <Label>Email</Label>
                        <Input v-model="form.email" />
                    </div>

                    <div>
                        <Label>Telemóvel</Label>
                        <Input v-model="form.phone" />
                    </div>

                    <div>
                        <Label>Grupo de Permissões</Label>
                        <Select v-model="form.role_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar grupo" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem
                                    v-for="r in roles"
                                    :key="r.id"
                                    :value="r.id"
                                >
                                    {{ r.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <Switch v-model:checked="form.is_active" />
                        <Label>Ativo</Label>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        :disabled="
                            form.processing ||
                            !form.name ||
                            !form.email ||
                            !form.role_id
                        "
                        @click="submit"
                    >
                        Guardar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <AlertDialog v-model:open="deleteDialogOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle> Apagar utilizador </AlertDialogTitle>

                    <AlertDialogDescription>
                        Tem a certeza que deseja apagar o utilizador
                        <strong>{{ userToDelete?.name }}</strong
                        >?
                        <br />
                        Esta ação não pode ser revertida.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel> Cancelar </AlertDialogCancel>

                    <AlertDialogAction
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                        @click="destroyUser"
                    >
                        Apagar
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
