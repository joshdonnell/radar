import { useFetch } from '@vueuse/core'
import { onMounted, ref } from 'vue'
import type { RadarConfig } from '@/types/radar'
import type { Scan } from '@/types/scan'

type LatestScanResponse = {
  scan: Scan | null
}

type RunScanResponse = {
  scan: Scan
}

export function useScanData(radarConfig: RadarConfig) {
  const scan = ref<Scan | null>(null)
  const loading = ref(true)

  const latestScanRequest = useFetch(radarConfig.latestScanUrl, {
    immediate: false,
    afterFetch(ctx) {
      const data = ctx.data as LatestScanResponse

      scan.value = data.scan

      return ctx
    },
    onFetchError(ctx) {
      console.error('Failed to load scan data:', ctx.error)

      return ctx
    },
  }).json<LatestScanResponse>()

  const runScanRequest = useFetch(
    radarConfig.scanUrl,
    {
      method: 'POST',
      headers: radarConfig.csrfToken
        ? { 'X-CSRF-TOKEN': radarConfig.csrfToken }
        : {},
    },
    {
      immediate: false,
      afterFetch(ctx) {
        const data = ctx.data as RunScanResponse

        scan.value = data.scan

        return ctx
      },
      onFetchError(ctx) {
        console.error('Failed to run scan:', ctx.error)

        return ctx
      },
    },
  ).json<RunScanResponse>()

  const loadScan = async () => {
    await latestScanRequest.execute()
  }

  const runScan = async () => {
    if (runScanRequest.isFetching.value) return

    await runScanRequest.execute()
  }

  onMounted(async () => {
    await loadScan()
    loading.value = false
  })

  return {
    scan,
    loading,
    scanning: runScanRequest.isFetching,
    runScan,
  }
}
