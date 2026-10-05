<script setup lang="ts">
import { useTimeAgo } from '@vueuse/core'
import { computed } from 'vue'
import RadarButton from '~/components/RadarButton.vue'
import { useRadarDashboard } from '~/composables/useRadarDashboard'

const { config, scan, scanning, runScan } = useRadarDashboard()

const scannedAt = computed(() => scan.value?.created_at ?? null)
const scannedAgo = useTimeAgo(
  computed(() => scannedAt.value ?? Date.now()),
  {
    updateInterval: 15000,
  },
)
const scannedAtLabel = computed(() =>
  scannedAt.value ? new Date(scannedAt.value).toLocaleString() : undefined,
)

const isProduction = computed(() => config.environment === 'production')
</script>

<template>
  <header
    class="sticky top-0 z-30 flex h-[57px] items-center justify-between gap-3 border-b border-border bg-bg/85 px-4 backdrop-blur-xl sm:px-6 lg:h-[62px] lg:px-7"
  >
    <div class="flex min-w-0 items-center gap-2.5">
      <span class="truncate text-sm font-semibold text-fg">
        {{ config.appName }}
      </span>
      <span
        class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider ring-1 ring-inset"
        :class="
          isProduction
            ? 'bg-danger/10 text-danger ring-danger/25'
            : 'bg-surface-2 text-muted ring-border-strong'
        "
      >
        {{ config.environment }}
      </span>
    </div>

    <div class="flex shrink-0 items-center gap-3">
      <p
        v-if="scannedAt"
        class="hidden items-center gap-2 text-[12px] text-muted sm:flex"
      >
        <span>Latest scan</span>
        <time
          :datetime="scannedAt"
          :title="scannedAtLabel"
          class="font-semibold text-fg"
        >
          {{ scannedAgo }}
        </time>
      </p>

      <RadarButton
        variant="primary"
        size="sm"
        :loading="scanning"
        :aria-label="
          scanning ? 'Scanning dependencies' : 'Run new dependency scan'
        "
        @click="runScan"
      >
        <template #icon>
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M8 5v14l11-7z" />
          </svg>
        </template>
        {{ scanning ? 'Scanning...' : 'Run Scan' }}
      </RadarButton>
    </div>
  </header>
</template>
