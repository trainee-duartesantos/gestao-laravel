import { usePage } from "@inertiajs/vue3";

export function usePermissions() {
    const page = usePage();

    const permissions = page.props.auth?.permissions ?? [];
    const roles = page.props.auth?.roles ?? [];

    const can = (permission) => {
        return permissions.includes(permission);
    };

    const hasRole = (role) => {
        return roles.includes(role);
    };

    return {
        can,
        hasRole,
    };
}
