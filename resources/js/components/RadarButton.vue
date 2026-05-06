<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary'
    size?: 'sm' | 'md'
    loading?: boolean
    disabled?: boolean
    ariaLabel?: string
  }>(),
  {
    variant: 'secondary',
    size: 'sm',
    loading: false,
    disabled: false,
    ariaLabel: undefined,
  },
)

defineEmits<{
  click: []
}>()

const variantClasses: Record<string, string> = {
  primary:
    'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-950/30 hover:bg-cyan-300 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B0F19]',
  secondary:
    'bg-cyan-500/10 text-cyan-300 ring-1 ring-inset ring-cyan-500/15 hover:bg-cyan-500/15',
}

const sizeClasses: Record<string, string> = {
  sm: 'px-2.5 py-1 text-[11px] gap-1.5',
  md: 'px-4 py-2.5 text-sm gap-2',
}
</script>

<template>
  <button
    class="inline-flex cursor-pointer items-center rounded-full font-semibold transition-all disabled:cursor-not-allowed disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40"
    :class="[variantClasses[variant], sizeClasses[size]]"
    :disabled="disabled || loading"
    :aria-label="ariaLabel"
    @click="$emit('click')"
  >
    <svg
      v-if="loading"
      class="animate-spin"
      :class="size === 'sm' ? 'h-3 w-3' : 'h-4 w-4'"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2.5"
    >
      <path d="M12 2v4m0 12v4M2 12h4m12 0h4" stroke-linecap="round" />
    </svg>
    <slot v-else name="icon" />
    <slot />
  </button>
</template>
