<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import EntitiesPagination from "@/Components/Entities/EntitiesPagination.vue";
import EntitiesPerPage from "@/Components/Entities/EntitiesPerPage.vue";
import EntitiesFilters from "@/Components/Entities/EntitiesFilters.vue";
import ContactModal from "@/Components/Contacts/ContactModal.vue";
import { Link } from "@inertiajs/vue3";

// props
const props = defineProps({
    entity: {
        type: Object,
        required: true,
    },
});

// estado
const contacts = ref([]);
const loading = ref(true);
const error = ref(null);

const search = ref("");
const status = ref("all");

const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const pagesCache = ref({});

let searchTimeout = null;

const showModal = ref(false);
const saving = ref(false);
const errors = ref({});
const mode = ref("create");
const editingId = ref(null);

const form = ref({
    name: "",
    role: "",
    email: "",
    phone: "",
    contact_role_id: null,
});

const showConfirm = ref(false);
const contactToToggle = ref(null);

const showDeleteConfirm = ref(false);
const contactToDelete = ref(null);

const confirmDelete = (contact) => {
    contactToDelete.value = contact;
    showDeleteConfirm.value = true;
};

const deleteConfirmed = async () => {
    if (!contactToDelete.value) return;

    await axios.delete(`/contacts/${contactToDelete.value.id}`);

    pagesCache.value = {};
    showDeleteConfirm.value = false;
    contactToDelete.value = null;

    await loadContacts(currentPage.value);
};

// helpers
const makeCacheKey = (page) =>
    `page=${page}|perPage=${perPage.value}|status=${status.value}|search=${search.value}`;

// carregar contactos
const loadContacts = async (page = 1) => {
    const cacheKey = makeCacheKey(page);

    if (pagesCache.value[cacheKey]) {
        const cached = pagesCache.value[cacheKey];
        contacts.value = cached.data;
        currentPage.value = cached.currentPage;
        lastPage.value = cached.lastPage;
        total.value = cached.total;
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(
            `/entities/${props.entity.id}/contacts/list`,
            {
                params: {
                    page,
                    per_page: perPage.value,
                    status: status.value,
                    search: search.value,
                },
            }
        );

        contacts.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        total.value = response.data.total;

        pagesCache.value[cacheKey] = {
            data: response.data.data,
            currentPage: response.data.current_page,
            lastPage: response.data.last_page,
            total: response.data.total,
        };
    } catch (e) {
        error.value = "Erro ao carregar contactos";
    } finally {
        loading.value = false;
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
};

// watchers
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        pagesCache.value = {};
        loadContacts(1);
    }, 300);
});

watch([status, perPage], () => {
    pagesCache.value = {};
    loadContacts(1);
});

const totalCount = computed(() => total.value);

const activeCount = computed(
    () => contacts.value.filter((c) => c.status === "active").length
);

const inactiveCount = computed(
    () => contacts.value.filter((c) => c.status === "inactive").length
);

const roles = ref([]);

const loadRoles = async () => {
    const res = await axios.get("/settings/contact-roles/list", {
        params: { per_page: 100 },
    });

    roles.value = res.data.data;
};

// toggle status
const confirmToggle = (contact) => {
    contactToToggle.value = contact;
    showConfirm.value = true;
};

const toggleStatusConfirmed = async () => {
    if (!contactToToggle.value) return;

    await axios.patch(`/contacts/${contactToToggle.value.id}/toggle-status`);

    pagesCache.value = {};
    showConfirm.value = false;
    contactToToggle.value = null;

    await loadContacts(currentPage.value);
};

const openCreateModal = () => {
    mode.value = "create";
    editingId.value = null;
    errors.value = {};
    form.value = {
        name: "",
        role: "",
        email: "",
        phone: "",
    };
    showModal.value = true;
};

const openEditModal = (contact) => {
    mode.value = "edit";
    editingId.value = contact.id;
    errors.value = {};
    form.value = {
        name: contact.name,
        email: contact.email,
        phone: contact.phone,
        contact_role_id: contact.contact_role_id,
    };
    showModal.value = true;
};

const saveContact = async () => {
    saving.value = true;

    try {
        if (mode.value === "create") {
            await axios.post(
                `/entities/${props.entity.id}/contacts`,
                form.value
            );
        } else {
            await axios.put(`/contacts/${editingId.value}`, form.value);
        }

        pagesCache.value = {};
        await loadContacts(1);
        showModal.value = false;
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadContacts();
    loadRoles();
});
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Contactos</h1>
                <p class="text-sm text-gray-500">
                    Entidade: <strong>{{ entity.name }}</strong>
                </p>
            </div>

            <div class="flex gap-2">
                <Link
                    :href="route('entities.page')"
                    class="bg-gray-500 text-white px-4 py-2 border rounded"
                >
                    ← Entidades
                </Link>

                <button
                    @click="openCreateModal"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    + Novo Contacto
                </button>
            </div>
        </div>

        <EntitiesFilters
            :filter-status="status"
            :search="search"
            :counts="{
                total: totalCount,
                active: activeCount,
                inactive: inactiveCount,
            }"
            @update:filterStatus="status = $event"
            @update:search="search = $event"
        />

        <!-- Per page + total -->
        <div class="flex justify-between items-center mb-3">
            <EntitiesPerPage v-model="perPage" />
            <span class="text-sm text-gray-500"> {{ total }} registos </span>
        </div>

        <!-- Tabela -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 table-fixed">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Nome</th>
                        <th class="px-3 py-2 text-left">Função</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">Telefone</th>
                        <th class="px-3 py-2 text-center w-28">Estado</th>
                        <th class="px-3 py-2 text-center w-32">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Skeleton -->
                    <tr v-if="loading" v-for="n in 5" :key="n">
                        <td colspan="6" class="px-3 py-4">
                            <div
                                class="h-4 bg-gray-200 rounded animate-pulse"
                            ></div>
                        </td>
                    </tr>

                    <!-- Dados -->
                    <tr
                        v-else
                        v-for="c in contacts"
                        :key="c.id"
                        class="border-t hover:bg-gray-50"
                    >
                        <td class="px-3 py-2">
                            {{ c.name }}
                        </td>
                        <td class="px-3 py-2">
                            {{ c.role || "—" }}
                        </td>
                        <td class="px-3 py-2">
                            {{ c.email || "—" }}
                        </td>
                        <td class="px-3 py-2">
                            {{ c.phone || "—" }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span
                                :class="[
                                    'px-2 py-1 rounded text-xs',
                                    c.status === 'active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-200 text-gray-600',
                                ]"
                            >
                                {{
                                    c.status === "active" ? "ativo" : "inativo"
                                }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center space-x-2">
                            <button
                                class="text-sm text-blue-600 hover:underline"
                                @click="openEditModal(c)"
                            >
                                Editar
                            </button>

                            <button
                                class="text-sm text-red-600 hover:underline"
                                @click="confirmToggle(c)"
                            >
                                {{
                                    c.status === "active"
                                        ? "Desativar"
                                        : "Ativar"
                                }}
                            </button>

                            <button
                                class="text-sm text-red-700 hover:underline"
                                @click="confirmDelete(c)"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>

                    <!-- Empty -->
                    <tr v-if="!loading && contacts.length === 0">
                        <td colspan="6" class="text-center py-6 text-gray-500">
                            Nenhum contacto encontrado
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <EntitiesPagination
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @change="loadContacts"
        />

        <!-- Modal confirmação ação -->
        <div
            v-if="showConfirm"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-3">Confirmação</h2>

                <p class="mb-6">
                    Tens a certeza que pretendes
                    <strong>
                        {{
                            contactToToggle?.status === "active"
                                ? "desativar"
                                : "ativar"
                        }}
                    </strong>

                    o contacto
                    <strong>{{ contactToToggle?.name }}</strong
                    >?
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        class="px-4 py-2 border rounded"
                        @click="showConfirm = false"
                    >
                        Cancelar
                    </button>

                    <button
                        class="px-4 py-2 bg-red-600 text-white rounded"
                        @click="toggleStatusConfirmed"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal confirmação eliminar -->
        <div
            v-if="showDeleteConfirm"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-3 text-red-600">
                    Eliminar contacto
                </h2>

                <p class="mb-6">
                    Tens a certeza que pretendes eliminar o contacto
                    <strong>{{ contactToDelete?.name }}</strong
                    >?
                    <br />
                    <span class="text-sm text-gray-500">
                        Esta ação não pode ser desfeita.
                    </span>
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        class="px-4 py-2 border rounded"
                        @click="showDeleteConfirm = false"
                    >
                        Cancelar
                    </button>

                    <button
                        class="px-4 py-2 bg-red-600 text-white rounded"
                        @click="deleteConfirmed"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Criar / Editar Contacto -->
        <ContactModal
            :show="showModal"
            :mode="mode"
            v-model="form"
            :roles="roles"
            :errors="errors"
            :saving="saving"
            @update:show="showModal = $event"
            @save="saveContact"
        />

        <!-- Erro -->
        <div v-if="error" class="mt-4 text-sm text-red-600">
            {{ error }}
        </div>
    </div>
</template>
