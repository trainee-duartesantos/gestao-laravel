<script setup>
import { ref, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

import { usePermissions } from "@/composables/usePermissions";
import { Toaster, toast } from "vue-sonner";

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/
const showingNavigationDropdown = ref(false);

/*
|--------------------------------------------------------------------------
| Permissions
|--------------------------------------------------------------------------
*/
const { can, hasRole } = usePermissions();

/*
|--------------------------------------------------------------------------
| Toasts (Laravel flash → Sonner)
|--------------------------------------------------------------------------
*/
const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;

        if (flash.success) {
            toast.success(flash.success);
        }

        if (flash.error) {
            toast.error(flash.error);
        }
    },
    { deep: true }
);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="border-b border-gray-100 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <!-- Left -->
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('dashboard')">
                                <ApplicationLogo
                                    class="block h-9 w-auto fill-current text-gray-800"
                                />
                            </Link>
                        </div>

                        <!-- Desktop Navigation -->
                        <div
                            class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                        >
                            <NavLink
                                v-if="can('clients.view')"
                                :href="route('clients')"
                                :active="route().current('clients')"
                            >
                                Clientes
                            </NavLink>

                            <NavLink
                                v-if="can('suppliers.view')"
                                :href="route('suppliers')"
                                :active="route().current('suppliers')"
                            >
                                Fornecedores
                            </NavLink>

                            <NavLink
                                v-if="can('articles.view')"
                                :href="route('articles.page')"
                                :active="route().current('articles.*')"
                            >
                                Artigos
                            </NavLink>

                            <NavLink
                                v-if="can('proposals.view')"
                                :href="route('proposals.page')"
                                :active="route().current('proposals.*')"
                            >
                                Propostas
                            </NavLink>

                            <NavLink
                                v-if="can('invoices.view')"
                                :href="route('invoices.page')"
                                :active="route().current('invoices.*')"
                            >
                                Faturas
                            </NavLink>

                            <NavLink
                                v-if="hasRole('Admin')"
                                :href="route('settings.countries.page')"
                                :active="route().current('settings.*')"
                            >
                                Configurações
                            </NavLink>
                            <NavLink
                                v-if="can('users.view')"
                                :href="route('users.page')"
                                :active="route().current('users.*')"
                            >
                                Utilizadores
                            </NavLink>

                            <NavLink
                                v-if="can('users.view')"
                                :href="route('roles.page')"
                                :active="route().current('roles.*')"
                            >
                                Roles
                            </NavLink>
                        </div>
                    </div>

                    <!-- Right -->
                    <div class="hidden sm:ms-6 sm:flex sm:items-center">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none"
                                >
                                    {{ $page.props.auth.user.name }}

                                    <svg
                                        class="-me-0.5 ms-2 h-4 w-4"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Perfil
                                </DropdownLink>

                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Terminar Sessão
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Mobile hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button
                            @click="
                                showingNavigationDropdown =
                                    !showingNavigationDropdown
                            "
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none"
                        >
                            <svg
                                class="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex':
                                            !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex':
                                            showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-show="showingNavigationDropdown" class="sm:hidden">
                <ResponsiveNavLink
                    v-if="can('clients.view')"
                    :href="route('clients')"
                >
                    Clientes
                </ResponsiveNavLink>

                <ResponsiveNavLink
                    v-if="can('suppliers.view')"
                    :href="route('suppliers')"
                >
                    Fornecedores
                </ResponsiveNavLink>

                <ResponsiveNavLink
                    v-if="can('articles.view')"
                    :href="route('articles.page')"
                >
                    Artigos
                </ResponsiveNavLink>

                <ResponsiveNavLink
                    v-if="can('invoices.view')"
                    :href="route('invoices.page')"
                >
                    Faturas
                </ResponsiveNavLink>

                <div class="border-t border-gray-200 pb-1 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-gray-800">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-sm font-medium text-gray-500">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">
                            Perfil
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Terminar Sessão
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <header v-if="$slots.header" class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Global Toasts -->
        <Toaster richColors position="top-right" />
    </div>
</template>
