<script setup>
import { ref, onMounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from "axios";

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";

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

const users = ref([]);
const roles = ref([]);
const open = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: "",
    email: "",
    phone: "",
    role_id: null,
    is_active: true,
});

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
            onSuccess: () => {
                open.value = false;
                loadUsers();
            },
        });
    } else {
        form.post(route("users.store"), {
            onSuccess: () => {
                open.value = false;
                loadUsers();
            },
        });
    }
};

const removeUser = (user) => {
    if (!confirm(`Apagar utilizador ${user.name}?`)) return;

    router.delete(route("users.destroy", user.id), {
        onSuccess: loadUsers,
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold">Utilizadores</h1>
            <Button @click="openCreate">Novo Utilizador</Button>
        </div>

        <table class="w-full border text-sm">
            <thead>
                <tr class="bg-muted">
                    <th class="p-2">Nome</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Telemóvel</th>
                    <th class="p-2">Grupo</th>
                    <th class="p-2">Estado</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="u in users" :key="u.id" class="border-t">
                    <td class="p-2">{{ u.name }}</td>
                    <td class="p-2">{{ u.email }}</td>
                    <td class="p-2">{{ u.phone ?? "-" }}</td>
                    <td class="p-2">{{ u.role }}</td>
                    <td class="p-2">
                        <span
                            :class="
                                u.is_active ? 'text-green-600' : 'text-red-600'
                            "
                        >
                            {{ u.is_active ? "Ativo" : "Inativo" }}
                        </span>
                    </td>
                    <td class="p-2 space-x-2">
                        <Button size="sm" variant="outline" @click="openEdit(u)"
                            >Editar</Button
                        >
                        <Button
                            size="sm"
                            variant="destructive"
                            @click="removeUser(u)"
                            >Apagar</Button
                        >
                    </td>
                </tr>
            </tbody>
        </table>

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
    </div>
</template>
