<script setup lang="ts">
import CodeSnippet from '~/components/CodeSnippet.vue'
import RadarEmptyState from '~/components/RadarEmptyState.vue'
import RadarSectionHeader from '~/components/RadarSectionHeader.vue'
import RadarStatusBadge from '~/components/RadarStatusBadge.vue'
import RadarVersionDiff from '~/components/RadarVersionDiff.vue'
import type { OutdatedPackageRecord } from '~/types/scan'
import { updateColor } from '~/utils/dashboard'

defineProps<{
  outdatedPackages: OutdatedPackageRecord[]
}>()
</script>

<template>
  <section
    id="radar-updates"
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-border bg-surface"
  >
    <RadarSectionHeader
      title="Updates"
      subtitle="Outdated direct dependencies found during the latest scan."
      :count="outdatedPackages.length + ' outdated'"
      count-color="warning"
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
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
          <polyline points="17 6 23 6 23 12" />
        </svg>
      </template>
    </RadarSectionHeader>

    <div v-if="outdatedPackages.length" class="divide-y divide-border">
      <article
        v-for="outdatedPackage in outdatedPackages"
        :key="outdatedPackage.id"
        class="flex flex-wrap items-start justify-between gap-3 px-5 py-4 transition-colors hover:bg-surface-2/30"
      >
        <div class="min-w-0">
          <h3 class="font-mono text-sm font-semibold text-fg">
            {{ outdatedPackage.package_name }}
          </h3>
          <RadarVersionDiff
            class="mt-2"
            tone="neutral"
            :current="outdatedPackage.current_version"
            :patched="outdatedPackage.latest_version"
          />
          <CodeSnippet
            v-if="outdatedPackage.suggested_command"
            :command="outdatedPackage.suggested_command"
            class="mt-3"
          />
        </div>
        <RadarStatusBadge
          :label="outdatedPackage.update_type"
          :color-class="updateColor(outdatedPackage.update_type)"
        />
      </article>
    </div>

    <RadarEmptyState
      v-else
      message="No outdated packages recorded in this scan."
    />
  </section>
</template>
