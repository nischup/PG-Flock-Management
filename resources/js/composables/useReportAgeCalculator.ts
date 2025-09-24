import { computed } from 'vue'

/**
 * Reactive report age calculator composable
 * @param start - start date string (YYYY-MM-DD or ISO format)
 */
export function useReportAgeCalculator(start: string) {
  const startDate = new Date(start)

  const age = computed(() => {
    const today = new Date()
    const diffDays = Math.floor(
      (today.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24)
    )
    const weeks = Math.floor(diffDays / 7)
    const days = diffDays % 7
    return `${weeks}+${days}` // ✅ report format
  })

  return age
}