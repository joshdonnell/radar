<script setup lang="ts">
defineProps<{
  message: string
  iconColor?: 'emerald' | 'slate'
}>()
</script>

<template>
  <div class="flex flex-col items-center justify-center px-5 py-10">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full ring-1 ring-inset"
      :class="
        iconColor === 'slate'
          ? 'bg-slate-500/5 ring-slate-500/10'
          : 'bg-emerald-500/5 ring-emerald-500/10'
      "
    >
      <slot name="icon">
        <svg
          class="h-4 w-4"
          :class="
            iconColor === 'slate' ? 'text-slate-500' : 'text-emerald-400/60'
          "
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <polyline points="20 6 9 17 4 12" />
        </svg>
      </slot>
    </div>
    <p class="mt-3 text-xs text-slate-500">
      <slot name="message">{{ message }}</slot>
    </p>
    <slot name="action" />
  </div>
</template>
