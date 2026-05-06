<script setup lang="ts">
import type { VulnerabilityRecord } from '@/types/scan'
import { parentUpdateCommand, severityColor } from '../utils/dashboard'
import CodeSnippet from './CodeSnippet.vue'
import RadarEmptyState from './RadarEmptyState.vue'
import RadarExternalLink from './RadarExternalLink.vue'
import RadarSectionHeader from './RadarSectionHeader.vue'

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
    class="scroll-mt-24 overflow-hidden rounded-2xl border border-white/[0.04] bg-[#0f1420]/80 shadow-xl shadow-black/20"
  >
    <radar-section-header
      title="Vulnerabilities"
      subtitle="Security advisories detected during the latest scan."
      :count="vulnerabilities.length + ' found'"
      count-color="rose"
    >
      <template #icon>
        <svg
          class="h-3.5 w-3.5"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
          />
          <line x1="12" y1="9" x2="12" y2="13" />
          <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg>
      </template>
    </radar-section-header>

    <div v-if="vulnerabilities.length" class="divide-y divide-white/[0.03]">
      <article
        v-for="vulnerability in vulnerabilities"
        :key="vulnerability.id"
        class="group px-5 py-4 transition-colors hover:bg-white/[0.01]"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <h3 class="truncate text-sm font-medium text-white">
                {{ vulnerability.package_name }}
              </h3>
              <code
                class="rounded bg-white/[0.04] px-1.5 py-0.5 text-[10px] font-mono text-slate-500 ring-1 ring-inset ring-white/[0.06]"
              >
                {{ vulnerability.installed_version }}
              </code>
            </div>
            <p class="mt-0.5 text-[11px] text-slate-600">
              {{ vulnerability.advisory_id }}
              <span v-if="vulnerability.cve">· {{ vulnerability.cve }}</span>
            </p>
          </div>
          <span
            class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset"
            :class="severityColor(vulnerability.severity)"
          >
            {{ vulnerability.severity }}
          </span>
        </div>

        <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
          <span
            class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider ring-1 ring-inset"
            :class="
              vulnerability.is_direct
                ? 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/15'
                : 'bg-slate-500/10 text-slate-400 ring-slate-500/15'
            "
          >
            {{
              vulnerability.is_direct
                ? 'Direct dependency'
                : 'Transitive dependency'
            }}
          </span>
          <radar-external-link
            v-if="vulnerability.advisory_url"
            :href="vulnerability.advisory_url"
            label="Advisory"
          />
          <radar-external-link
            v-if="vulnerability.cve"
            :href="`https://cve.mitre.org/cgi-bin/cvename.cgi?name=${vulnerability.cve}`"
            :label="vulnerability.cve"
            color="amber"
          />
        </div>

        <code-snippet
          v-if="vulnerability.is_direct && vulnerability.suggested_command"
          :command="vulnerability.suggested_command"
          class="mt-2.5"
        />

        <div
          v-else-if="
            !vulnerability.is_direct && vulnerability.required_by?.length
          "
          class="mt-2.5 space-y-1.5"
        >
          <div
            class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500"
          >
            <svg
              class="h-3 w-3 text-slate-600"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M23 21v-2a4 4 0 00-3-3.87" />
              <path d="M16 3.13a4 4 0 010 7.75" />
            </svg>
            Required by
            <button
              v-for="parent in vulnerability.required_by"
              :key="parent"
              class="inline-flex cursor-pointer items-center gap-1 rounded bg-white/[0.04] px-1.5 py-0.5 font-mono text-[10px] text-slate-400 ring-1 ring-inset ring-white/[0.06] transition-colors hover:bg-white/[0.08] hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
              @click="$emit('inspectPackage', parent)"
            >
              {{ parent }}
            </button>
          </div>
          <code-snippet
            v-for="parent in vulnerability.required_by"
            :key="parent"
            :command="parentUpdateCommand(vulnerability.ecosystem, parent)"
          />
        </div>
      </article>
    </div>

    <radar-empty-state
      v-else
      message="No vulnerabilities recorded in this scan."
    />
  </section>
</template>
