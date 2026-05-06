import { computed, ref } from 'vue'
import type { Ref } from 'vue'
import type { Scan } from '@/types/scan'

export function usePackageFilter(scan: Ref<Scan | null>) {
  const packageSearch = ref('')
  const packagePageSize = 10
  const showAllPackages = ref(false)

  const clearSearch = () => {
    packageSearch.value = ''
  }

  const filteredPackages = computed(() => {
    if (!scan.value) return []

    const term = packageSearch.value.trim().toLowerCase()

    if (!term) return scan.value.packages

    return scan.value.packages.filter(
      (pkg) =>
        pkg.name.toLowerCase().includes(term) ||
        pkg.installed_version.toLowerCase().includes(term),
    )
  })

  const visiblePackages = computed(() => {
    if (showAllPackages.value) return filteredPackages.value

    return filteredPackages.value.slice(0, packagePageSize)
  })

  const hasMorePackages = computed(() => {
    return filteredPackages.value.length > packagePageSize
  })

  const togglePackages = () => {
    showAllPackages.value = !showAllPackages.value
  }

  return {
    packageSearch,
    showAllPackages,
    clearSearch,
    filteredPackages,
    visiblePackages,
    hasMorePackages,
    togglePackages,
  }
}
