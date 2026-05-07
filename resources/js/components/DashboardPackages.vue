<script setup lang="ts">
import type {
  PackageRelationFilter,
  PackageTypeFilter,
} from '@/composables/usePackageFilter'
import type { PackageRecord } from '@/types/scan'
import RadarBadge from './RadarBadge.vue'

defineProps<{
  packageSearch: string
  packageRelationFilter: PackageRelationFilter
  packageTypeFilter: PackageTypeFilter
  filteredPackages: PackageRecord[]
  visiblePackages: PackageRecord[]
  hasMorePackages: boolean
  showAllPackages: boolean
}>()

defineEmits<{
  clearSearch: []
  togglePackages: []
  'update:packageSearch': [value: string]
  'update:packageRelationFilter': [value: PackageRelationFilter]
  'update:packageTypeFilter': [value: PackageTypeFilter]
}>()

const relationFilters: { label: string; value: PackageRelationFilter }[] = [
  { label: 'All', value: 'all' },
  { label: 'Direct', value: 'direct' },
  { label: 'Transitive', value: 'transitive' },
]

const typeFilters: { label: string; value: PackageTypeFilter }[] = [
  { label: 'All types', value: 'all' },
  { label: 'Production', value: 'production' },
  { label: 'Development', value: 'development' },
  { label: 'Peer', value: 'peer' },
]
</script>

<template>
  <section
    id="radar-packages"
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-white/[0.04] bg-[#0f1420]/80 shadow-xl shadow-black/20"
  >
    <div
      class="flex flex-col gap-3 border-b border-white/[0.04] px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div class="flex items-center gap-3">
        <span
          class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-400 ring-1 ring-inset ring-cyan-500/15"
        >
          <svg
            class="h-3.5 w-3.5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"
            />
            <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
            <line x1="12" y1="22.08" x2="12" y2="12" />
          </svg>
        </span>
        <div>
          <h2 class="text-sm font-semibold text-white">Packages</h2>
          <p class="text-[11px] text-slate-500">
            Direct and transitive dependencies captured from lock files.
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <div class="relative">
          <svg
            class="absolute left-2.5 top-1/2 h-3 w-3 -translate-y-1/2 text-slate-600"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
          <input
            :value="packageSearch"
            type="text"
            placeholder="Search packages..."
            class="h-7 w-full rounded-lg border border-white/[0.06] bg-white/[0.02] py-1 pl-7 pr-7 text-[11px] text-white placeholder-slate-600 ring-0 transition-all hover:border-white/[0.10] focus:border-cyan-400/30 focus:outline-none focus:ring-1 focus:ring-cyan-400/10 sm:w-52"
            @input="
              $emit(
                'update:packageSearch',
                ($event.target as HTMLInputElement).value,
              )
            "
          />
          <button
            v-if="packageSearch"
            class="absolute right-1 top-1/2 inline-flex h-5 w-5 -translate-y-1/2 cursor-pointer items-center justify-center rounded text-slate-500 transition-colors hover:bg-white/[0.06] hover:text-slate-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
            aria-label="Clear search"
            @click="$emit('clearSearch')"
          >
            <svg
              class="h-3 w-3"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
            >
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
        <radar-badge color="cyan" size="sm">
          {{ filteredPackages.length }} packages
        </radar-badge>
      </div>
    </div>

    <div
      class="flex flex-col gap-2 border-b border-white/[0.04] px-5 py-3 sm:flex-row sm:items-center sm:justify-between"
    >
      <div
        class="inline-flex w-full flex-wrap gap-1 rounded-lg bg-white/[0.025] p-1 ring-1 ring-inset ring-white/[0.05] sm:w-auto"
      >
        <button
          v-for="filter in relationFilters"
          :key="filter.value"
          class="h-7 cursor-pointer rounded-md px-2.5 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
          :class="
            packageRelationFilter === filter.value
              ? 'bg-cyan-400/10 text-cyan-200 ring-1 ring-inset ring-cyan-400/15'
              : 'text-slate-500 hover:bg-white/[0.04] hover:text-slate-300'
          "
          :aria-pressed="packageRelationFilter === filter.value"
          @click="$emit('update:packageRelationFilter', filter.value)"
        >
          {{ filter.label }}
        </button>
      </div>

      <div
        class="inline-flex w-full flex-wrap gap-1 rounded-lg bg-white/[0.025] p-1 ring-1 ring-inset ring-white/[0.05] sm:w-auto"
      >
        <button
          v-for="filter in typeFilters"
          :key="filter.value"
          class="h-7 cursor-pointer rounded-md px-2.5 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
          :class="
            packageTypeFilter === filter.value
              ? 'bg-cyan-400/10 text-cyan-200 ring-1 ring-inset ring-cyan-400/15'
              : 'text-slate-500 hover:bg-white/[0.04] hover:text-slate-300'
          "
          :aria-pressed="packageTypeFilter === filter.value"
          @click="$emit('update:packageTypeFilter', filter.value)"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>

    <div v-if="filteredPackages.length" class="overflow-x-auto">
      <table class="min-w-full text-left text-[13px]">
        <thead
          class="bg-white/[0.015] text-[10px] font-semibold uppercase tracking-wider text-slate-600"
        >
          <tr>
            <th class="px-5 py-2.5">Package</th>
            <th class="px-5 py-2.5">Version</th>
            <th class="px-5 py-2.5">Type</th>
            <th class="px-5 py-2.5">Required by</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/[0.03]">
          <tr
            v-for="pkg in visiblePackages"
            :key="pkg.id"
            class="transition-colors hover:bg-white/[0.015]"
          >
            <td class="px-5 py-2.5">
              <div class="flex items-center gap-2">
                <span class="font-medium text-slate-200">{{ pkg.name }}</span>
                <span
                  v-if="pkg.is_direct"
                  class="rounded bg-emerald-500/10 px-1 py-px text-[9px] font-bold uppercase tracking-wider text-emerald-300 ring-1 ring-inset ring-emerald-500/15"
                >
                  Direct
                </span>
              </div>
            </td>
            <td class="px-5 py-2.5">
              <code
                class="rounded bg-white/[0.03] px-1.5 py-px text-[11px] font-mono text-slate-500 ring-1 ring-inset ring-white/[0.05]"
              >
                {{ pkg.installed_version }}
              </code>
            </td>
            <td class="px-5 py-2.5 text-slate-500">
              {{ pkg.dependency_type }}
            </td>
            <td class="px-5 py-2.5 text-[11px] text-slate-600">
              {{ pkg.required_by?.length ? pkg.required_by.join(', ') : '—' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="hasMorePackages"
      class="flex items-center justify-center border-t border-white/[0.03] px-5 py-2.5"
    >
      <button
        class="inline-flex cursor-pointer items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-medium text-slate-500 transition-colors hover:bg-white/[0.03] hover:text-slate-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
        @click="$emit('togglePackages')"
      >
        <span v-if="showAllPackages"> Show less </span>
        <span v-else>
          Show {{ filteredPackages.length - visiblePackages.length }} more
        </span>
        <svg
          class="h-3 w-3"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <polyline
            :points="showAllPackages ? '18 15 12 9 6 15' : '6 9 12 15 18 9'"
          />
        </svg>
      </button>
    </div>

    <div
      v-else-if="!filteredPackages.length"
      class="flex flex-col items-center justify-center px-5 py-10"
    >
      <div
        class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-500/5 ring-1 ring-inset ring-slate-500/10"
      >
        <svg
          class="h-4 w-4 text-slate-500"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
      </div>
      <p class="mt-3 text-xs text-slate-500">
        <span v-if="packageSearch"
          >No packages match "{{ packageSearch }}".</span
        >
        <span
          v-else-if="
            packageRelationFilter !== 'all' || packageTypeFilter !== 'all'
          "
          >No packages match the selected filters.</span
        >
        <span v-else>No packages recorded in this scan.</span>
      </p>
      <button
        v-if="packageSearch"
        class="mt-2 cursor-pointer rounded px-1 text-[11px] font-medium text-cyan-400 transition-colors hover:text-cyan-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
        @click="$emit('clearSearch')"
      >
        Clear search
      </button>
    </div>
  </section>
</template>
