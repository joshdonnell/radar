<script setup lang="ts">
defineProps<{
  current: string
  patched?: string | null
}>()
</script>

<template>
  <span class="inline-flex items-center gap-2">
    <code
      class="rounded-md border border-danger/20 bg-danger/10 px-2 py-0.5 font-mono text-[12px] text-danger"
    >
      {{ current }}
    </code>

    <template v-if="patched">
      <svg
        class="h-3.5 w-3.5 text-dim"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="M5 12h14M13 6l6 6-6 6" />
      </svg>
      <code
        class="rounded-md border border-success/20 bg-success/10 px-2 py-0.5 font-mono text-[12px] text-success"
      >
        {{ patched }}
      </code>
    </template>
  </span>
</template>
