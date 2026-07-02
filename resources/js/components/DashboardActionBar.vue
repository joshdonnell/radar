<script setup lang="ts">
import RadarButton from '~/components/RadarButton.vue'
import { useRadarDashboard } from '~/composables/useRadarDashboard'

const { scan, scanning, runScan } = useRadarDashboard()
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2.5 text-[13px] text-muted">
      <svg
        class="h-[15px] w-[15px] text-dim"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <circle cx="12" cy="12" r="9" />
        <path d="M12 7v5l3 2" />
      </svg>
      <span>Latest scan</span>
      <span class="font-semibold text-fg">
        {{ scan?.created_at_human ?? 'Time unknown' }}
      </span>
    </div>

    <div class="flex items-center gap-2.5">
      <!-- Export PDF button is deferred to a later PR. -->
      <RadarButton
        variant="primary"
        :loading="scanning"
        :aria-label="
          scanning ? 'Scanning dependencies' : 'Run new dependency scan'
        "
        @click="runScan"
      >
        <template #icon>
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M8 5v14l11-7z" />
          </svg>
        </template>
        {{ scanning ? 'Scanning...' : 'Run Scan' }}
      </RadarButton>
    </div>
  </div>
</template>
