<script setup lang="ts">
import { ref } from 'vue'
import type { RadarConfig } from '@/types/radar'
import Dashboard from './Dashboard.vue'
import RadarNavItem from './RadarNavItem.vue'

defineProps<{
  radarConfig: RadarConfig
}>()

const hasScan = ref(false)

const navItems = [
  {
    id: 'radar-overview',
    label: 'Overview',
    color: 'cyan' as const,
    icon: ['M3 3h7v7H3z', 'M14 3h7v7h-7z', 'M14 14h7v7h-7z', 'M3 14h7v7H3z'],
  },
  {
    id: 'radar-vulnerabilities',
    label: 'Vulnerabilities',
    color: 'rose' as const,
    icon: [
      'M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
      'M12 9v4',
      'M12 17h.01',
    ],
  },
  {
    id: 'radar-packages',
    label: 'Packages',
    color: 'cyan' as const,
    icon: [
      'M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z',
      'M3.27 6.96L12 12.01l8.73-5.05',
      'M12 22.08V12',
    ],
  },
  {
    id: 'radar-updates',
    label: 'Updates',
    color: 'amber' as const,
    icon: ['M23 6l-9.5 9.5-5-5L1 18', 'M17 6h6v6'],
  },
  {
    id: 'radar-abandoned',
    label: 'Abandoned',
    color: 'amber' as const,
    icon: ['M12 8v4', 'M12 16h.01'],
    viewBoxCircle: true,
  },
]

const activeSection = ref(navItems[0]?.id ?? '')
const dashboard = ref<{ scrollToSection: (id: string) => void } | null>(null)

const scrollTo = (id: string) => {
  dashboard.value?.scrollToSection(id)
}
</script>

<template>
  <div id="radar" class="relative min-h-screen bg-[#0B0F19]">
    <!-- Ambient background -->
    <div
      class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_80%_50%_at_20%_-10%,_rgba(14,165,233,0.10),_transparent),radial-gradient(ellipse_60%_40%_at_80%_-5%,_rgba(45,212,191,0.06),_transparent),radial-gradient(ellipse_50%_50%_at_50%_100%,_rgba(99,102,241,0.04),_transparent)]"
    />
    <div
      class="pointer-events-none fixed inset-0 opacity-40"
      style="
        background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);
      "
    />

    <!-- Mobile section nav -->
    <div
      v-if="hasScan"
      class="sticky top-[73px] z-20 border-b border-white/[0.04] bg-[#0B0F19]/80 backdrop-blur-2xl lg:hidden"
    >
      <div
        class="mx-auto flex max-w-6xl items-center gap-1 overflow-x-auto px-6 py-2 lg:px-8"
      >
        <a
          v-for="item in navItems"
          :key="item.id"
          :href="`#${item.id}`"
          class="shrink-0 cursor-pointer rounded-full px-3 py-1 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
          :class="
            activeSection === item.id
              ? item.color === 'rose'
                ? 'bg-rose-400/10 text-rose-300'
                : item.color === 'amber'
                  ? 'bg-amber-400/10 text-amber-300'
                  : 'bg-cyan-400/10 text-cyan-300'
              : 'text-slate-500 hover:bg-white/[0.03] hover:text-slate-300'
          "
          @click.prevent="scrollTo(item.id)"
        >
          {{ item.label }}
        </a>
      </div>
    </div>

    <aside
      v-if="hasScan"
      class="fixed inset-y-0 left-0 hidden w-60 border-r z-10 border-white/[0.04] bg-[#0B0F19]/80 backdrop-blur-2xl lg:block"
      aria-label="Section navigation"
    >
      <div class="flex h-14 items-center border-b border-white/[0.04] px-5">
        <div class="flex items-center gap-2.5">
          <div
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-500/15"
          >
            <svg
              class="h-4 w-4"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
            >
              <circle cx="12" cy="12" r="3" />
              <path d="M12 2v4m0 12v4M2 12h4m12 0h4" stroke-linecap="round" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold tracking-tight text-white">
              Laravel Radar
            </p>
            <p
              class="text-[10px] font-medium uppercase tracking-widest text-slate-500"
            >
              Dependency health
            </p>
          </div>
        </div>
      </div>

      <nav
        class="space-y-0.5 p-3 text-[13px] font-medium"
        aria-label="Dashboard sections"
      >
        <radar-nav-item
          v-for="item in navItems"
          :key="item.id"
          :href="`#${item.id}`"
          :label="item.label"
          :is-active="activeSection === item.id"
          :color="item.color"
          @click="scrollTo(item.id)"
        >
          <template #icon>
            <svg
              class="h-3 w-3"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
            >
              <circle v-if="item.viewBoxCircle" cx="12" cy="12" r="10" />
              <path
                v-for="(d, i) in item.icon"
                :key="i"
                :d="d"
                stroke-linecap="round"
              />
            </svg>
          </template>
        </radar-nav-item>
      </nav>
    </aside>

    <main class="relative" :class="hasScan ? 'lg:pl-60' : ''">
      <header
        class="sticky top-0 z-30 border-b border-white/[0.04] bg-[#0B0F19]/60 backdrop-blur-2xl"
        role="banner"
      >
        <div
          class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4 lg:px-8"
        >
          <div>
            <p
              class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-400/70"
            >
              Radar
            </p>
            <h1 class="mt-1 text-xl font-semibold tracking-tight text-white">
              Dependency Health
            </h1>
          </div>

          <div
            class="inline-flex items-center gap-2 rounded-full border border-white/[0.06] bg-white/[0.02] px-3 py-1 text-[11px] font-medium text-slate-400"
          >
            <span class="relative flex h-1.5 w-1.5">
              <span
                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400/40 opacity-75"
              />
              <span
                class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"
              />
            </span>
            Read-only dashboard
          </div>
        </div>
      </header>

      <section
        class="mx-auto px-6 py-6 lg:px-8"
        :class="hasScan ? 'max-w-6xl' : 'max-w-4xl'"
        aria-live="polite"
      >
        <dashboard
          ref="dashboard"
          :radar-config="radarConfig"
          @active-section="activeSection = $event"
          @scan-state="hasScan = $event"
        />
      </section>
    </main>
  </div>
</template>
