<script setup lang="ts">
import DashboardScanStatus from '~/components/DashboardScanStatus.vue'
import DashboardStats from '~/components/DashboardStats.vue'
import DashboardWarnings from '~/components/DashboardWarnings.vue'
import { useRadarDashboard } from '~/composables/useRadarDashboard'

const { scan } = useRadarDashboard()
</script>

<template>
  <div class="flex flex-col gap-4">
    <DashboardScanStatus />
    <DashboardWarnings v-if="scan?.warnings.length" :warnings="scan.warnings" />
    <DashboardStats />
  </div>
</template>
