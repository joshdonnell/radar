<script setup lang="ts">
import RadarEmptyState from '~/components/RadarEmptyState.vue'
import RadarSectionHeader from '~/components/RadarSectionHeader.vue'
import RadarStatusBadge from '~/components/RadarStatusBadge.vue'
import type { AbandonedPackageRecord } from '~/types/scan'
import { relationColor } from '~/utils/dashboard'

defineProps<{
  abandonedPackages: AbandonedPackageRecord[]
}>()
</script>

<template>
  <section
    id="radar-abandoned"
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-border bg-surface"
  >
    <RadarSectionHeader
      title="Abandoned packages"
      subtitle="Composer packages marked as abandoned in the lock file."
      :count="abandonedPackages.length + ' abandoned'"
      count-color="abandoned"
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
          <path d="M6 4h12v9a6 6 0 01-12 0z" />
          <path d="M9 21h6M12 17v4" />
        </svg>
      </template>
    </RadarSectionHeader>

    <div v-if="abandonedPackages.length" class="divide-y divide-border">
      <article
        v-for="abandonedPackage in abandonedPackages"
        :key="abandonedPackage.id"
        class="px-5 py-4 transition-colors hover:bg-surface-2/30"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <h3 class="font-mono text-sm font-semibold text-fg">
              {{ abandonedPackage.package_name }}
            </h3>
            <p class="mt-1.5 flex items-center gap-2 text-[12px] text-muted">
              <code
                class="rounded-md border border-border-strong bg-surface-2 px-2 py-0.5 font-mono text-muted"
              >
                {{ abandonedPackage.installed_version }}
              </code>
              <span>{{ abandonedPackage.dependency_type }}</span>
            </p>
          </div>
          <RadarStatusBadge
            :label="abandonedPackage.is_direct ? 'Direct' : 'Transitive'"
            :color-class="relationColor(abandonedPackage.is_direct)"
          />
        </div>
        <p
          v-if="abandonedPackage.replacement_package"
          class="mt-2.5 text-[13px] text-fg/80"
        >
          Replacement:
          <span class="font-mono">{{
            abandonedPackage.replacement_package
          }}</span>
        </p>
        <p
          v-if="abandonedPackage.recommendation"
          class="mt-1 text-[12px] text-muted"
        >
          {{ abandonedPackage.recommendation }}
        </p>
      </article>
    </div>

    <RadarEmptyState
      v-else
      message="No abandoned packages recorded in this scan."
    />
  </section>
</template>
