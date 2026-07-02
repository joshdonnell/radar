<script setup lang="ts">
import RadarBadge from '~/components/RadarBadge.vue'
import type {
  PackageRelationFilter,
  PackageTypeFilter,
} from '~/composables/usePackageFilter'
import { useRadarDashboard } from '~/composables/useRadarDashboard'

const {
  packageSearch,
  packageRelationFilter,
  packageTypeFilter,
  showAllPackages,
  clearSearch,
  filteredPackages,
  visiblePackages,
  hasMorePackages,
  togglePackages,
} = useRadarDashboard()

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
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-border bg-surface"
  >
    <div
      class="flex flex-col gap-3 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div class="flex items-center gap-3">
        <span
          class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-2 text-muted ring-1 ring-inset ring-border-strong"
        >
          <svg
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M21 8l-9-5-9 5 9 5 9-5z" />
            <path d="M3 8v8l9 5 9-5V8" />
            <path d="M12 13v8" />
          </svg>
        </span>
        <div>
          <h2 class="text-[15px] font-semibold text-fg">Packages</h2>
          <p class="text-[12px] text-muted">
            Direct and transitive dependencies captured from lock files.
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <div class="relative">
          <svg
            class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-dim"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
          <input
            v-model="packageSearch"
            type="text"
            placeholder="Search packages..."
            class="h-8 w-full rounded-lg border border-border bg-inset py-1 pl-8 pr-8 text-[12px] text-fg placeholder-dim transition-colors hover:border-border-strong focus:border-border-strong focus:outline-none sm:w-52"
          />
          <button
            v-if="packageSearch"
            type="button"
            class="absolute right-1 top-1/2 inline-flex h-5 w-5 -translate-y-1/2 cursor-pointer items-center justify-center rounded text-muted transition-colors hover:bg-surface-2 hover:text-fg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
            aria-label="Clear search"
            @click="clearSearch"
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
        <RadarBadge color="neutral" size="md">
          {{ filteredPackages.length }} packages
        </RadarBadge>
      </div>
    </div>

    <div
      class="flex flex-col gap-2 border-b border-border px-5 py-3 sm:flex-row sm:items-center sm:justify-between"
    >
      <div
        class="inline-flex w-full flex-wrap gap-1 rounded-lg bg-surface-2/60 p-1 ring-1 ring-inset ring-border sm:w-auto"
      >
        <button
          v-for="filter in relationFilters"
          :key="filter.value"
          type="button"
          class="h-7 cursor-pointer rounded-md px-2.5 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
          :class="
            packageRelationFilter === filter.value
              ? 'bg-fg/10 text-fg ring-1 ring-inset ring-border-strong'
              : 'text-muted hover:text-fg'
          "
          :aria-pressed="packageRelationFilter === filter.value"
          @click="packageRelationFilter = filter.value"
        >
          {{ filter.label }}
        </button>
      </div>

      <div
        class="inline-flex w-full flex-wrap gap-1 rounded-lg bg-surface-2/60 p-1 ring-1 ring-inset ring-border sm:w-auto"
      >
        <button
          v-for="filter in typeFilters"
          :key="filter.value"
          type="button"
          class="h-7 cursor-pointer rounded-md px-2.5 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
          :class="
            packageTypeFilter === filter.value
              ? 'bg-fg/10 text-fg ring-1 ring-inset ring-border-strong'
              : 'text-muted hover:text-fg'
          "
          :aria-pressed="packageTypeFilter === filter.value"
          @click="packageTypeFilter = filter.value"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>

    <div v-if="filteredPackages.length" class="overflow-x-auto">
      <table class="min-w-full text-left text-[13px]">
        <thead
          class="bg-surface-2/40 text-[10px] font-semibold uppercase tracking-wider text-dim"
        >
          <tr>
            <th class="px-5 py-2.5">Package</th>
            <th class="px-5 py-2.5">Version</th>
            <th class="px-5 py-2.5">Type</th>
            <th class="px-5 py-2.5">Required by</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          <tr
            v-for="pkg in visiblePackages"
            :key="pkg.id"
            class="transition-colors hover:bg-surface-2/30"
          >
            <td class="px-5 py-2.5">
              <div class="flex items-center gap-2">
                <span class="font-mono font-medium text-fg/90">{{
                  pkg.name
                }}</span>
                <span
                  v-if="pkg.is_direct"
                  class="rounded bg-success/10 px-1.5 py-px text-[9px] font-bold uppercase tracking-wider text-success ring-1 ring-inset ring-success/25"
                >
                  Direct
                </span>
              </div>
            </td>
            <td class="px-5 py-2.5">
              <code
                class="rounded-md border border-border-strong bg-surface-2 px-2 py-px font-mono text-[11px] text-muted"
              >
                {{ pkg.installed_version }}
              </code>
            </td>
            <td class="px-5 py-2.5 text-muted">
              {{ pkg.dependency_type }}
            </td>
            <td class="px-5 py-2.5 text-[11px] text-dim">
              {{ pkg.required_by?.length ? pkg.required_by.join(', ') : '—' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="hasMorePackages"
      class="flex items-center justify-center border-t border-border px-5 py-2.5"
    >
      <button
        type="button"
        class="inline-flex cursor-pointer items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-medium text-muted transition-colors hover:bg-surface-2 hover:text-fg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
        @click="togglePackages"
      >
        <span v-if="showAllPackages">Show less</span>
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
      class="flex flex-col items-center justify-center px-5 py-8"
    >
      <div
        class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-2 ring-1 ring-inset ring-border-strong"
      >
        <svg
          class="h-3.5 w-3.5 text-muted"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
      </div>
      <p class="mt-2.5 text-xs text-muted">
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
        type="button"
        class="mt-2 cursor-pointer rounded px-1 text-[11px] font-medium text-fg transition-colors hover:text-fg/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
        @click="clearSearch"
      >
        Clear search
      </button>
    </div>
  </section>
</template>
