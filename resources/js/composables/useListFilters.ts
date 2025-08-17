import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export function useListFilters(options?: {
  routeName?: string; 
  filters?: { search?: string; per_page?: number; page?: number }; 
  extraParams?: Record<string, any>;
}) {
  const search = ref(options?.filters?.search ?? '');
  const perPage = ref(options?.filters?.per_page ?? 10);
  const page = ref(options?.filters?.page ?? 1);
  const routeName = ref(options?.routeName ?? '');

  // Watch each filter individually
  watch([search, perPage, page], () => {
    if (!routeName.value) return;

    router.get(routeName.value, {
      search: search.value,
      per_page: perPage.value,
      page: page.value,
      ...(options?.extraParams ?? {}),
    }, {
      preserveState: true,
      replace: true,
    });
  }, { immediate: false });

  return { search, perPage, page };
}
