<script setup lang="ts">
import type { AbandonedPackageRecord } from '@/types/scan'
import RadarEmptyState from './RadarEmptyState.vue'
import RadarSectionHeader from './RadarSectionHeader.vue'

defineProps<{
  abandonedPackages: AbandonedPackageRecord[]
}>()
</script>

<template>
  <section
    id="radar-abandoned"
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-white/[0.04] bg-[#0f1420]/80 shadow-xl shadow-black/20"
  >
    <radar-section-header
      title="Abandoned packages"
      subtitle="Composer packages marked as abandoned in the lock file."
      :count="abandonedPackages.length + ' abandoned'"
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
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
      </template>
    </radar-section-header>

    <div v-if="abandonedPackages.length" class="divide-y divide-white/[0.03]">
      <article
        v-for="abandonedPackage in abandonedPackages"
        :key="abandonedPackage.id"
        class="group px-5 py-4 transition-colors hover:bg-white/[0.01]"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <h3 class="text-sm font-medium text-white">
              {{ abandonedPackage.package_name }}
            </h3>
            <p class="mt-0.5 text-[11px] text-slate-600">
              <code
                class="rounded bg-white/[0.03] px-1 py-px font-mono text-slate-500 ring-1 ring-inset ring-white/[0.05]"
              >
                {{ abandonedPackage.installed_version }}
              </code>
              <span class="mx-1 text-slate-800">·</span>
              {{ abandonedPackage.dependency_type }}
            </p>
          </div>
          <span
            class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset"
            :class="
              abandonedPackage.is_direct
                ? 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/15'
                : 'bg-slate-500/10 text-slate-400 ring-slate-500/15'
            "
          >
            {{ abandonedPackage.is_direct ? 'Direct' : 'Transitive' }}
          </span>
        </div>
        <p
          v-if="abandonedPackage.replacement_package"
          class="mt-2 text-xs text-slate-400"
        >
          Replacement: {{ abandonedPackage.replacement_package }}
        </p>
        <p
          v-if="abandonedPackage.recommendation"
          class="mt-1 text-[11px] text-slate-600"
        >
          {{ abandonedPackage.recommendation }}
        </p>
      </article>
    </div>

    <radar-empty-state
      v-else
      message="No abandoned packages recorded in this scan."
    />
  </section>
</template>
