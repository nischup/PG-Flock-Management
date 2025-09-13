import { computed } from 'vue';

export function useAuditLog() {
    const getEventBadgeColor = (event: string) => {
        const colors: Record<string, string> = {
            created: 'bg-green-100 text-green-800',
            updated: 'bg-blue-100 text-blue-800',
            deleted: 'bg-red-100 text-red-800',
            viewed: 'bg-gray-100 text-gray-800',
            login: 'bg-purple-100 text-purple-800',
            logout: 'bg-orange-100 text-orange-800',
        };
        return colors[event] || 'bg-gray-100 text-gray-800';
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleString();
    };

    const getModelDisplayName = (modelType: string) => {
        return modelType.split('\\').pop() || modelType;
    };

    const getEventIcon = (event: string) => {
        const icons: Record<string, string> = {
            created: 'plus',
            updated: 'edit',
            deleted: 'trash',
            viewed: 'eye',
            login: 'log-in',
            logout: 'log-out',
        };
        return icons[event] || 'activity';
    };

    const getEventDescription = (event: string) => {
        const descriptions: Record<string, string> = {
            created: 'A new record was created',
            updated: 'An existing record was modified',
            deleted: 'A record was removed',
            viewed: 'A record was accessed',
            login: 'User logged into the system',
            logout: 'User logged out of the system',
        };
        return descriptions[event] || 'System activity occurred';
    };

    return {
        getEventBadgeColor,
        formatDate,
        getModelDisplayName,
        getEventIcon,
        getEventDescription,
    };
}
