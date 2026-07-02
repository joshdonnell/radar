<script setup lang="ts">
import { useClipboard } from '@vueuse/core'

const props = defineProps<{
  text: string
}>()

const { copied, copy } = useClipboard({
  copiedDuring: 1300,
  legacy: true,
})

const copyText = () => {
  void copy(props.text)
}
</script>

<template>
  <button
    type="button"
    class="group inline-flex cursor-pointer items-center gap-1.5 rounded-sm border bg-surface-2 px-2 py-0.5 align-middle font-mono text-[11px] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
    :class="
      copied
        ? 'border-success/40 text-success'
        : 'border-border-strong text-muted hover:border-dim hover:text-fg'
    "
    :aria-label="copied ? 'Copied to clipboard' : `Copy “${text}”`"
    @click="copyText"
  >
    <span>{{ text }}</span>

    <span class="inline-flex h-3 w-3 items-center justify-center">
      <svg
        v-if="copied"
        class="h-3 w-3"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <polyline points="20 6 9 17 4 12" />
      </svg>
      <svg
        v-else
        class="h-3 w-3"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <rect x="9" y="9" width="11" height="11" rx="2" />
        <path d="M5 15V5a2 2 0 012-2h8" />
      </svg>
    </span>
  </button>
</template>
