<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";

import EntitiesPagination from "@/Components/Entities/EntitiesPagination.vue";
import EntitiesPerPage from "@/Components/Entities/EntitiesPerPage.vue";
import ArticleModal from "@/Components/Articles/ArticleModal.vue";

import { usePage } from "@inertiajs/vue3";

const page = usePage();
const vatRates = page.props.vatRates;

// =======================
// ESTADO
// =======================
const articles = ref([]);
const loading = ref(true);
const error = ref(null);

const search = ref("");
const filterStatus = ref("all");
const filterType = ref("");
const perPage = ref(10);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const showModal = ref(false);
const saving = ref(false);
const errors = ref({});
const mode = ref("create");
const editingId = ref(null);

const showConfirm = ref(false);
const articleToToggle = ref(null);

const form = ref({
    code: "",
    name: "",
    type: "product",
    price: "",
    vat_rate_id: vatRates[0]?.id ?? null,
});

const openCreateModal = () => {
    mode.value = "create";
    editingId.value = null;
    errors.value = {};
    form.value = {
        code: "",
        name: "",
        type: "product",
        price: "",
        vat_rate_id: vatRates[0]?.id ?? null,
    };
    showModal.value = true;
};

const openEditModal = (article) => {
    mode.value = "edit";
    editingId.value = article.id;
    errors.value = {};
    form.value = {
        code: article.code,
        name: article.name,
        type: article.type,
        price: article.price,
        vat_rate_id: article.vat_rate_id,
    };
    showModal.value = true;
};

const createArticle = async () => {
    saving.value = true;
    try {
        await axios.post("/articles", form.value);
        showModal.value = false;
        loadArticles();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    } finally {
        saving.value = false;
    }
};

const updateArticle = async () => {
    saving.value = true;
    try {
        await axios.put(`/articles/${editingId.value}`, form.value);
        showModal.value = false;
        loadArticles();
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    } finally {
        saving.value = false;
    }
};

let searchTimeout = null;

// =======================
// LOAD ARTICLES
// =======================
const loadArticles = async (page = 1) => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get("/articles/list", {
            params: {
                page,
                per_page: perPage.value,
                search: search.value,
                status: filterStatus.value,
                type: filterType.value,
            },
        });

        articles.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        total.value = response.data.total;
    } catch (e) {
        error.value =
            e.response?.status === 500
                ? "Erro interno do servidor."
                : "Não foi possível carregar os artigos.";
    } finally {
        loading.value = false;
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
};

const toggleStatus = async (article) => {
    try {
        await axios.patch(`/articles/${article.id}/toggle-status`);
        loadArticles(currentPage.value);
    } catch (e) {
        alert("Erro ao alterar o estado do artigo.");
    }
};

const confirmToggle = (article) => {
    articleToToggle.value = article;
    showConfirm.value = true;
};

const toggleStatusConfirmed = async () => {
    if (!articleToToggle.value) return;

    await axios.patch(`/articles/${articleToToggle.value.id}/toggle-status`);

    showConfirm.value = false;
    articleToToggle.value = null;
    loadArticles(currentPage.value);
};

// =======================
// WATCHERS
// =======================
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadArticles(1);
    }, 300);
});

watch([filterStatus, filterType, perPage], () => {
    loadArticles(1);
});

// =======================
// HELPERS
// =======================
const typeLabel = (type) => {
    return type === "product" ? "Produto" : "Serviço";
};

// =======================
// INIT
// =======================
onMounted(() => {
    loadArticles();
});
</script>

<template>
    <div class="p-6">
        <!-- HEADER -->
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold">Artigos</h1>

            <button
                class="bg-blue-600 text-white px-4 py-2 rounded"
                @click="openCreateModal"
            >
                + Novo Artigo
            </button>
        </div>

        <!-- FILTROS -->
        <div class="flex flex-wrap gap-3 mb-4">
            <input
                v-model="search"
                type="text"
                placeholder="Pesquisar por código ou nome"
                class="border rounded px-3 py-2 w-64"
            />

            <select v-model="filterType" class="border rounded px-3 py-2">
                <option value="">Todos os tipos</option>
                <option value="product">Produtos</option>
                <option value="service">Serviços</option>
            </select>

            <select v-model="filterStatus" class="border rounded px-3 py-2">
                <option value="all">Todos os estados</option>
                <option value="active">Ativos</option>
                <option value="inactive">Inativos</option>
            </select>

            <EntitiesPerPage v-model="perPage" />
        </div>

        <!-- ERRO -->
        <div
            v-if="error"
            class="mb-4 rounded border border-red-200 bg-red-50 p-4 text-red-700"
        >
            {{ error }}
        </div>

        <!-- TABELA -->
        <table class="min-w-full border border-gray-200 table-fixed">
            <thead class="bg-gray-100">
                <tr>
                    <th class="w-32 px-3 py-2 text-left">Código</th>
                    <th class="px-3 py-2 text-left">Nome</th>
                    <th class="w-28 px-3 py-2 text-center">Tipo</th>
                    <th class="w-32 px-3 py-2 text-right">Preço</th>
                    <th class="w-24 px-3 py-2 text-center">IVA</th>
                    <th class="w-24 px-3 py-2 text-center">Estado</th>
                    <th class="w-24 px-3 py-2 text-center">Ações</th>
                </tr>
            </thead>

            <tbody>
                <!-- LOADING -->
                <tr v-if="loading">
                    <td colspan="6" class="py-10 text-center text-gray-500">
                        A carregar artigos...
                    </td>
                </tr>

                <!-- DATA -->
                <tr
                    v-else
                    v-for="a in articles"
                    :key="a.id"
                    class="border-t hover:bg-gray-50"
                >
                    <td class="px-3 py-2 font-mono text-sm">
                        {{ a.code }}
                    </td>
                    <td class="px-3 py-2">
                        {{ a.name }}
                    </td>
                    <td class="px-3 py-2 text-center">
                        {{ typeLabel(a.type) }}
                    </td>
                    <td class="px-3 py-2 text-right">{{ a.price }} €</td>
                    <td class="px-3 py-2 text-center">{{ a.vat }}%</td>
                    <td class="px-3 py-2 text-center">
                        <span
                            :class="[
                                'px-2 py-1 rounded text-xs font-medium',
                                a.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-200 text-gray-600',
                            ]"
                        >
                            {{ a.status === "active" ? "Ativo" : "Inativo" }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center space-x-3">
                        <button
                            class="text-sm hover:underline"
                            :class="
                                a.status === 'active'
                                    ? 'text-red-600'
                                    : 'text-green-600'
                            "
                            @click="
                                a.status === 'active'
                                    ? confirmToggle(a)
                                    : toggleStatus(a)
                            "
                        >
                            {{ a.status === "active" ? "Desativar" : "Ativar" }}
                        </button>
                    </td>
                </tr>

                <!-- EMPTY -->
                <tr v-if="!loading && articles.length === 0">
                    <td colspan="6" class="py-10 text-center text-gray-500">
                        Nenhum artigo encontrado.
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- MODAL CONFIRMAÇÃO DESATIVAR -->
        <div
            v-if="showConfirm"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-3 text-red-600">
                    Desativar artigo
                </h2>

                <p class="mb-6">
                    Tens a certeza que pretendes desativar o artigo
                    <strong>{{ articleToToggle?.name }}</strong
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
                        Desativar
                    </button>
                </div>
            </div>
        </div>

        <!-- PAGINAÇÃO -->
        <EntitiesPagination
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @change="loadArticles"
        />

        <ArticleModal
            :show="showModal"
            :mode="mode"
            v-model="form"
            :errors="errors"
            :saving="saving"
            :vat-rates="vatRates"
            @update:show="showModal = $event"
            @save="mode === 'create' ? createArticle() : updateArticle()"
        />
    </div>
</template>
