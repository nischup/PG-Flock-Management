import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export function useListFilters(options?: {
  routeName?: string; 
  filters?: { 
    search?: string; 
    per_page?: number; 
    page?: number;
    status?: string;
    feed_type?: string;
    date_from?: string;
    date_to?: string;
    [key: string]: any;
  }; 
  extraParams?: Record<string, any>;
}) {
  const search = ref(options?.filters?.search ?? '');
  const perPage = ref(options?.filters?.per_page ?? 10);
  const page = ref(options?.filters?.page ?? 1);
  const status = ref(options?.filters?.status ?? '');
  const feedType = ref(options?.filters?.feed_type ?? '');
  const supplierType = ref(options?.filters?.supplier_type ?? '');
  const dateFrom = ref(options?.filters?.date_from ?? '');
  const dateTo = ref(options?.filters?.date_to ?? '');
  const routeName = ref(options?.routeName ?? '');

  // Watch each filter individually
  watch([search, perPage, page, status, feedType, supplierType, dateFrom, dateTo], () => {
    if (!routeName.value) return;

    const params: Record<string, any> = {
      search: search.value,
      per_page: perPage.value,
      page: page.value,
      ...(options?.extraParams ?? {}),
    };

    // Only add filter params if they have values
    if (status.value) params.status = status.value;
    if (feedType.value) params.feed_type = feedType.value;
    if (supplierType.value) params.supplier_type = supplierType.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;

    router.get(routeName.value, params, {
      preserveState: true,
      replace: true,
    });
  }, { immediate: false });

  return { search, perPage, page, status, feedType, supplierType, dateFrom, dateTo };
}
