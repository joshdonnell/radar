<script setup lang="ts">
import Dashboard from '~/components/Dashboard.vue'
import RadarToasts from '~/components/RadarToasts.vue'
import { provideRadarDashboard } from '~/composables/useRadarDashboard'
import AppSidebar from '~/layouts/Sidebar.vue'
import AppTopbar from '~/layouts/Topbar.vue'
import type { RadarConfig } from '~/types/radar'

const props = defineProps<{
  radarConfig: RadarConfig
}>()

const { scan } = provideRadarDashboard(props.radarConfig)
</script>

<template>
  <div class="min-h-screen bg-bg text-fg" :class="{ 'lg:pl-[260px]': scan }">
    <AppSidebar v-if="scan" />

    <div class="flex min-h-screen flex-col">
      <AppTopbar v-if="scan" />

      <main class="flex-1">
        <div class="mx-auto w-full max-w-[1180px] px-4 py-6 sm:px-6 lg:px-7">
          <Dashboard />
        </div>
      </main>
    </div>

    <RadarToasts />
  </div>
</template>
