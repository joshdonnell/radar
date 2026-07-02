<script setup lang="ts">
import RadarEmptyState from '~/components/RadarEmptyState.vue'
import RadarSectionHeader from '~/components/RadarSectionHeader.vue'
import VulnerabilityCard from '~/components/VulnerabilityCard.vue'
import type { VulnerabilityRecord } from '~/types/scan'

defineProps<{
  vulnerabilities: VulnerabilityRecord[]
}>()

defineEmits<{
  inspectPackage: [packageName: string]
}>()
</script>

<template>
  <section
    id="radar-vulnerabilities"
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-border bg-surface"
  >
    <RadarSectionHeader
      title="Vulnerabilities"
      subtitle="Security advisories detected during the latest scan."
      :count="vulnerabilities.length + ' found'"
      count-color="danger"
    >
      <template #icon>
        <svg
          class="h-4 w-4"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M12 3l9 16H3z" />
          <path d="M12 10v4M12 17h.01" />
        </svg>
      </template>
    </RadarSectionHeader>

    <div v-if="vulnerabilities.length" class="divide-y divide-border">
      <VulnerabilityCard
        v-for="vulnerability in vulnerabilities"
        :key="vulnerability.id"
        :vulnerability="vulnerability"
        @inspect-package="$emit('inspectPackage', $event)"
      />
    </div>

    <RadarEmptyState
      v-else
      message="No vulnerabilities recorded in this scan."
    />
  </section>
</template>
