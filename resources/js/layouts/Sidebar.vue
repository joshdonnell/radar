<script setup lang="ts">
import AppLogo from '~/components/AppLogo.vue'
import RadarNavItem from '~/components/RadarNavItem.vue'
import { useRadarDashboard } from '~/composables/useRadarDashboard'

type NavItem = {
  id: string
  label: string
  paths: string[]
}

const navItems: NavItem[] = [
  {
    id: 'radar-overview',
    label: 'Overview',
    paths: ['M3 3h7v7H3z', 'M14 3h7v7h-7z', 'M3 14h7v7H3z', 'M14 14h7v7h-7z'],
  },
  {
    id: 'radar-vulnerabilities',
    label: 'Vulnerabilities',
    paths: ['M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z'],
  },
  {
    id: 'radar-packages',
    label: 'Packages',
    paths: ['M21 8l-9-5-9 5 9 5 9-5z', 'M3 8v8l9 5 9-5V8', 'M12 13v8'],
  },
  {
    id: 'radar-updates',
    label: 'Updates',
    paths: [
      'M3 12a9 9 0 019-9 9 9 0 016.7 3L21 8',
      'M21 3v5h-5',
      'M21 12a9 9 0 01-9 9 9 9 0 01-6.7-3L3 16',
      'M3 21v-5h5',
    ],
  },
  {
    id: 'radar-abandoned',
    label: 'Abandoned',
    paths: ['M6 4h12v9a6 6 0 01-12 0z', 'M9 21h6', 'M12 17v4'],
  },
]

const { scan, activeSection, scrollToSection } = useRadarDashboard()

const vulnerabilityCount = (id: string): number | null => {
  if (id !== 'radar-vulnerabilities') return null

  return scan.value?.vulnerability_count ?? null
}
</script>

<template>
  <!-- Mobile: horizontal section nav -->
  <div
    class="sticky top-[57px] z-20 border-b border-border bg-bg/85 backdrop-blur-xl lg:hidden"
  >
    <div class="flex items-center gap-1 overflow-x-auto px-4 py-2">
      <button
        v-for="item in navItems"
        :key="item.id"
        class="shrink-0 cursor-pointer rounded-full px-3 py-1 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
        :class="
          activeSection === item.id
            ? 'bg-surface text-fg'
            : 'text-muted hover:bg-surface/60 hover:text-fg'
        "
        @click="scrollToSection(item.id)"
      >
        {{ item.label }}
      </button>
    </div>
  </div>

  <!-- Desktop: fixed sidebar -->
  <aside
    class="fixed inset-y-0 left-0 z-20 hidden w-[260px] flex-col gap-6 border-r border-border bg-bg px-3.5 py-4 lg:flex"
    aria-label="Section navigation"
  >
    <div class="px-1.5 pt-1">
      <AppLogo />
    </div>

    <nav class="flex flex-col gap-0.5" aria-label="Dashboard sections">
      <p
        class="px-2.5 pb-1 pt-1.5 text-[11px] font-semibold tracking-[0.09em] text-dim"
      >
        MENU
      </p>

      <RadarNavItem
        v-for="item in navItems"
        :key="item.id"
        :href="`#${item.id}`"
        :label="item.label"
        :is-active="activeSection === item.id"
        @click="scrollToSection(item.id)"
      >
        <template #icon>
          <svg
            class="h-[17px] w-[17px]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path v-for="(d, i) in item.paths" :key="i" :d="d" />
          </svg>
        </template>

        <template v-if="vulnerabilityCount(item.id)" #trailing>
          <span
            class="rounded-full border border-danger/25 bg-danger/10 px-2 py-px text-[11px] font-semibold text-danger"
          >
            {{ vulnerabilityCount(item.id) }}
          </span>
        </template>
      </RadarNavItem>
    </nav>
  </aside>
</template>
