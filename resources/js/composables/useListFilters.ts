
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const search = ref('');
const perPage = ref(10);
const page = ref(1);
const routeName = ref('');

export function useListFilters(options?: {
  routeName?: string;
  filters?: { search?: string; per_page?: number; page?: number };
  extraParams?: Record<string, any>;
}) {
  if (options?.routeName) routeName.value = options.routeName;

  if (options?.filters) {
    search.value = options.filters.search ?? '';
    perPage.value = options.filters.per_page ?? 10;
    page.value = options.filters.page ?? 1;
  }

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
  });

  return { search, perPage, page };
}
