<script setup lang="ts">
import { useClipboard } from '@vueuse/core'

const props = defineProps<{
  command: string
}>()

const { copied, copy } = useClipboard({
  copiedDuring: 1300,
  legacy: true,
})

const copyCommand = () => {
  void copy(props.command)
}
</script>

<template>
  <button
    type="button"
    class="flex w-full cursor-pointer items-center gap-3 rounded-lg border border-border bg-inset px-3.5 py-3 text-left transition-colors hover:border-border-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
    :aria-label="`Copy command: ${command}`"
    @click="copyCommand"
  >
    <span class="shrink-0 font-mono text-sm font-semibold text-success"
      >&gt;</span
    >
    <code class="min-w-0 flex-1 truncate font-mono text-[12.5px] text-fg/80">
      {{ command }}
    </code>
    <span v-if="copied" class="shrink-0 text-[11px] font-semibold text-success">
      Copied
    </span>
    <svg
      v-if="copied"
      class="h-[15px] w-[15px] shrink-0 text-success"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <polyline points="20 6 9 17 4 12" />
    </svg>
    <svg
      v-else
      class="h-[15px] w-[15px] shrink-0 text-dim"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="1.7"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <rect x="9" y="9" width="11" height="11" rx="2" />
      <path d="M5 15V5a2 2 0 012-2h8" />
    </svg>
  </button>
</template>
