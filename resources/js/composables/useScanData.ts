import { useIntervalFn, useNow } from '@vueuse/core'
import { computed, onMounted, ref } from 'vue'
import { useToasts } from '~/composables/useToasts'
import type { RadarConfig } from '~/types/radar'
import type { Scan, ScanStatus } from '~/types/scan'

type LatestScanResponse = {
  scan: Scan | null
  status: ScanStatus
}

type RunScanResponse = {
  status: ScanStatus
}

const pollInterval = 3000

/** A queued scan that has not started after this long probably has no worker. */
const stalledAfter = 2 * 60 * 1000

class RequestError extends Error {
  constructor(public readonly status: number) {
    super(`Request failed with status ${status}`)
  }
}

function requestErrorMessage(error: unknown, action: string): string {
  if (!(error instanceof RequestError)) {
    return `Could not ${action}. Check your connection and try again.`
  }

  if (error.status === 419) {
    return 'Your session has expired. Reload the page and try again.'
  }

  if (error.status === 429) {
    return 'Too many scan requests. Wait a minute and try again.'
  }

  if (error.status === 403) {
    return `You are not authorised to ${action}.`
  }

  return `Could not ${action} (HTTP ${error.status}). Check your application logs.`
}

export function useScanData(radarConfig: RadarConfig) {
  const toasts = useToasts()
  const now = useNow({ interval: 15000 })

  const scan = ref<Scan | null>(null)
  const status = ref<ScanStatus>({
    pending: false,
    queued_at: null,
    error: null,
  })
  const loading = ref(true)
  const loadError = ref<string | null>(null)
  const requesting = ref(false)

  const request = async <TResponse>(
    url: string,
    method: 'GET' | 'POST' = 'GET',
  ): Promise<TResponse> => {
    const response = await fetch(url, {
      method,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': radarConfig.csrfToken,
      },
    })

    if (!response.ok) {
      throw new RequestError(response.status)
    }

    return (await response.json()) as TResponse
  }

  const applyStatus = (nextStatus: ScanStatus) => {
    const wasPending = status.value.pending

    status.value = nextStatus

    if (!wasPending || nextStatus.pending) return

    if (nextStatus.error) {
      toasts.error(`The scan failed: ${nextStatus.error}`)

      return
    }

    toasts.success('Scan complete.')
  }

  const loadScan = async () => {
    try {
      const data = await request<LatestScanResponse>(radarConfig.latestScanUrl)

      scan.value = data.scan
      loadError.value = null
      applyStatus(data.status)
    } catch (error) {
      loadError.value = requestErrorMessage(error, 'load the latest scan')
    }
  }

  const polling = useIntervalFn(
    async () => {
      await loadScan()

      if (!status.value.pending) {
        polling.pause()
      }
    },
    pollInterval,
    { immediate: false },
  )

  const runScan = async () => {
    if (requesting.value || status.value.pending) return

    requesting.value = true

    try {
      const data = await request<RunScanResponse>(radarConfig.scanUrl, 'POST')

      status.value = data.status

      if (data.status.pending) {
        polling.resume()

        return
      }

      await loadScan()

      if (data.status.error) {
        toasts.error(`The scan failed: ${data.status.error}`)
      } else {
        toasts.success('Scan complete.')
      }
    } catch (error) {
      toasts.error(requestErrorMessage(error, 'start a scan'))
    } finally {
      requesting.value = false
    }
  }

  const scanning = computed(() => requesting.value || status.value.pending)

  const scanStalled = computed(() => {
    if (!status.value.pending || !status.value.queued_at) return false

    return (
      now.value.getTime() - new Date(status.value.queued_at).getTime() >
      stalledAfter
    )
  })

  onMounted(async () => {
    await loadScan()
    loading.value = false

    if (status.value.pending) {
      polling.resume()
    }
  })

  return {
    scan,
    loading,
    loadError,
    scanning,
    scanStalled,
    runScan,
    reload: loadScan,
  }
}
