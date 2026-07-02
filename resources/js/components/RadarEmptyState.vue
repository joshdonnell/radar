<script setup lang="ts">
defineProps<{
  message: string
}>()
</script>

<template>
  <div class="flex flex-col items-center justify-center px-5 py-8">
    <div
      class="flex h-8 w-8 items-center justify-center rounded-full bg-success/[0.08] ring-1 ring-inset ring-success/15"
    >
      <svg
        class="h-3.5 w-3.5 text-success"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
      >
        <polyline points="20 6 9 17 4 12" />
      </svg>
    </div>
    <p class="mt-2.5 text-xs text-muted">{{ message }}</p>
  </div>
</template>
