<script setup lang="ts">
import type { OutdatedPackageRecord } from '@/types/scan'
import { updateColor } from '../utils/dashboard'
import CodeSnippet from './CodeSnippet.vue'
import RadarEmptyState from './RadarEmptyState.vue'
import RadarSectionHeader from './RadarSectionHeader.vue'

defineProps<{
  outdatedPackages: OutdatedPackageRecord[]
}>()
</script>

<template>
  <section
    id="radar-updates"
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-white/[0.04] bg-[#0f1420]/80 shadow-xl shadow-black/20"
  >
    <radar-section-header
      title="Updates"
      subtitle="Outdated direct dependencies found during the latest scan."
      :count="outdatedPackages.length + ' outdated'"
      count-color="amber"
    >
      <template #icon>
        <svg
          class="h-3.5 w-3.5"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
          <polyline points="17 6 23 6 23 12" />
        </svg>
      </template>
    </radar-section-header>

    <div v-if="outdatedPackages.length" class="divide-y divide-white/[0.03]">
      <article
        v-for="outdatedPackage in outdatedPackages"
        :key="outdatedPackage.id"
        class="group px-5 py-4 transition-colors hover:bg-white/[0.01]"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <h3 class="text-sm font-medium text-white">
              {{ outdatedPackage.package_name }}
            </h3>
            <p
              class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-600"
            >
              <code
                class="rounded bg-white/[0.03] px-1 py-px font-mono text-slate-500 ring-1 ring-inset ring-white/[0.05]"
              >
                {{ outdatedPackage.current_version }}
              </code>
              <svg
                class="h-2.5 w-2.5 text-slate-700"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
              >
                <polyline points="9 18 15 12 9 6" />
              </svg>
              <code
                class="rounded bg-emerald-500/5 px-1 py-px font-mono text-emerald-300/80 ring-1 ring-inset ring-emerald-500/10"
              >
                {{ outdatedPackage.latest_version }}
              </code>
            </p>
          </div>
          <span
            class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset"
            :class="updateColor(outdatedPackage.update_type)"
          >
            {{ outdatedPackage.update_type }}
          </span>
        </div>
        <code-snippet
          v-if="outdatedPackage.suggested_command"
          :command="outdatedPackage.suggested_command"
          class="mt-2.5"
        />
      </article>
    </div>

    <radar-empty-state
      v-else
      message="No outdated packages recorded in this scan."
    />
  </section>
</template>
