import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
export function usePermissions() {
    const page = usePage();

    console.log(page.props.auth?.user?.permissions);
    const permissions = page.props.auth?.user?.permissions ?? [];

    console.log(permissions);

    function can(permission?: string) {
        if (!permission) return true;
        return permissions.includes(permission);
    }

  return { permissions, can };
}
