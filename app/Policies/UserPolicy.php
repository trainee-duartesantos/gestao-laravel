<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Ver lista de utilizadores
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    /**
     * Criar utilizador
     */
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    /**
     * Atualizar utilizador
     */
    public function update(User $user, User $target): bool
    {
        // Não pode editar-se a si próprio para desativar
        if ($user->id === $target->id) {
            return true;
        }

        // Apenas Admin pode editar Admin
        if ($target->hasRole('Admin') && ! $user->hasRole('Admin')) {
            return false;
        }

        return $user->can('users.edit');
    }

    /**
     * Apagar utilizador
     */
    public function delete(User $user, User $target): bool
    {
        // Nunca se pode apagar a si próprio
        if ($user->id === $target->id) {
            return false;
        }

        // Admin não pode ser apagado
        if ($target->hasRole('Admin')) {
            return false;
        }

        return $user->can('users.delete');
    }

    /**
     * Atribuir / alterar role
     */
    public function assignRole(User $user, User $target): bool
    {
        // Apenas Admin pode atribuir roles
        return $user->hasRole('Admin');
    }
}
