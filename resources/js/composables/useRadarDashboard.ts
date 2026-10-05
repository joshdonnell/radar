import { useTitle } from '@vueuse/core'
import { computed, inject, provide, shallowRef, triggerRef } from 'vue'
import type { InjectionKey } from 'vue'
import { usePackageFilter } from '~/composables/usePackageFilter'
import { useScanData } from '~/composables/useScanData'
import { useSectionObserver } from '~/composables/useSectionObserver'
import type { RadarConfig } from '~/types/radar'
import { buildPackageBreakdown } from '~/utils/packages'

const sectionScrollOffset = 80

export const dashboardSections = [
  'radar-overview',
  'radar-vulnerabilities',
  'radar-packages',
  'radar-updates',
  'radar-abandoned',
] as const

function createRadarDashboard(config: RadarConfig) {
  // Section elements are registered from template ref callbacks, which run
  // during render. Keeping them in a plain map (and signalling changes with
  // triggerRef) stops that render from depending on the state it writes.
  const sectionElements = new Map<string, HTMLElement | null>()
  const sectionsChanged = shallowRef(0)

  const { scan, loading, loadError, scanning, scanStalled, runScan, reload } =
    useScanData(config)
  const packageFilter = usePackageFilter(scan)
  const packageBreakdown = computed(() =>
    buildPackageBreakdown(scan.value?.packages ?? []),
  )

  useTitle(
    computed(() => {
      const count = scan.value?.vulnerability_count ?? 0
      const title = `Radar | ${config.appName}`

      return count > 0 ? `(${count}) ${title}` : title
    }),
  )

  const { activeSection } = useSectionObserver(() => {
    void sectionsChanged.value

    return dashboardSections.map((id) => sectionElements.get(id) ?? null)
  }, dashboardSections[0])

  const registerSection = (id: string, element: HTMLElement | null) => {
    if (sectionElements.get(id) === element) return

    sectionElements.set(id, element)
    triggerRef(sectionsChanged)
  }

  const scrollToSection = (id: string) => {
    const element = sectionElements.get(id)

    if (!element) return

    window.scrollTo({
      top:
        element.getBoundingClientRect().top +
        window.scrollY -
        sectionScrollOffset,
      behavior: 'smooth',
    })
  }

  const inspectPackage = (packageName: string) => {
    packageFilter.packageSearch.value = packageName
    scrollToSection('radar-packages')
  }

  return {
    config,
    scan,
    loading,
    loadError,
    scanning,
    scanStalled,
    runScan,
    reload,
    packageBreakdown,
    activeSection,
    registerSection,
    scrollToSection,
    inspectPackage,
    ...packageFilter,
  }
}

export type RadarDashboard = ReturnType<typeof createRadarDashboard>

const radarDashboardKey: InjectionKey<RadarDashboard> =
  Symbol('radar-dashboard')

export function provideRadarDashboard(config: RadarConfig): RadarDashboard {
  const dashboard = createRadarDashboard(config)

  provide(radarDashboardKey, dashboard)

  return dashboard
}

export function useRadarDashboard(): RadarDashboard {
  const dashboard = inject(radarDashboardKey)

  if (!dashboard) {
    throw new Error(
      'useRadarDashboard must be used within a component that calls provideRadarDashboard.',
    )
  }

  return dashboard
}
