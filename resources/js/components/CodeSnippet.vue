<script setup lang="ts">
import { useClipboard } from '@vueuse/core'

const props = defineProps<{
  command: string
}>()

const { copied, copy } = useClipboard({
  copiedDuring: 2000,
  legacy: true,
})

const copyCommand = () => {
  void copy(props.command)
}
</script>

<template>
  <div
    class="group relative flex items-center gap-2 overflow-hidden rounded-lg bg-slate-950/60 px-3 py-2 ring-1 ring-inset ring-white/[0.06] transition-colors hover:ring-white/[0.10]"
  >
    <svg
      class="h-3.5 w-3.5 shrink-0 text-cyan-400/70"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2.5"
    >
      <polyline points="4 17 10 11 4 5" />
      <line x1="12" y1="19" x2="20" y2="19" />
    </svg>
    <code class="min-w-0 truncate font-mono text-xs text-cyan-200">
      {{ command }}
    </code>
    <button
      class="ml-auto inline-flex shrink-0 cursor-pointer items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold uppercase tracking-wider transition-all"
      :class="
        copied
          ? 'bg-emerald-400/10 text-emerald-300 ring-1 ring-inset ring-emerald-400/20'
          : 'bg-white/[0.04] text-slate-400 opacity-0 ring-1 ring-inset ring-white/[0.06] group-hover:opacity-100 focus-visible:opacity-100 hover:bg-white/[0.08] hover:text-white'
      "
      aria-label="Copy command"
      @click="copyCommand"
    >
      <svg
        v-if="copied"
        class="h-3 w-3"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
      >
        <polyline points="20 6 9 17 4 12" />
      </svg>
      <svg
        v-else
        class="h-3 w-3"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
      >
        <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
      </svg>
      {{ copied ? 'Copied' : 'Copy' }}
    </button>
  </div>
</template>
