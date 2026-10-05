<script setup lang="ts">
import RadarSpinner from '~/components/RadarSpinner.vue'
import { useRadarDashboard } from '~/composables/useRadarDashboard'

const { scanning, scanStalled } = useRadarDashboard()
</script>

<template>
  <div
    v-if="scanning"
    class="flex items-start gap-3 rounded-2xl border border-border-strong bg-surface px-5 py-4"
    role="status"
    aria-live="polite"
  >
    <RadarSpinner class="!h-5 !w-5 shrink-0" />
    <div class="min-w-0">
      <p class="text-[13px] font-semibold text-fg">
        {{
          scanStalled
            ? 'Waiting for the scan to start'
            : 'Scanning dependencies'
        }}
      </p>
      <p class="mt-0.5 text-[12px] text-muted">
        <template v-if="scanStalled">
          The scan is still queued. Make sure a queue worker is running, for
          example with
          <code class="font-mono text-fg/80">php artisan queue:work</code>.
        </template>
        <template v-else>
          The scan runs in the background. Results will appear here when it
          finishes.
        </template>
      </p>
    </div>
  </div>
</template>
