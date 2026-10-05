<script setup lang="ts">
import { useToasts } from '~/composables/useToasts'

const { toasts, dismiss } = useToasts()
</script>

<template>
  <div
    class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex flex-col items-end gap-2 sm:left-auto sm:right-6 sm:w-96"
    aria-live="polite"
  >
    <TransitionGroup
      enter-from-class="translate-y-2 opacity-0"
      enter-active-class="transition duration-200"
      leave-to-class="opacity-0"
      leave-active-class="transition duration-150"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex w-full items-start gap-3 rounded-xl border bg-surface px-4 py-3 text-[13px] shadow-lg shadow-black/20"
        :class="
          toast.tone === 'error' ? 'border-danger/30' : 'border-success/30'
        "
        :role="toast.tone === 'error' ? 'alert' : 'status'"
      >
        <span
          class="mt-1 h-2 w-2 shrink-0 rounded-full"
          :class="toast.tone === 'error' ? 'bg-danger' : 'bg-success'"
          aria-hidden="true"
        />
        <p class="min-w-0 flex-1 break-words text-fg/90">{{ toast.message }}</p>
        <button
          type="button"
          class="-mr-1 inline-flex h-5 w-5 shrink-0 cursor-pointer items-center justify-center rounded text-muted transition-colors hover:text-fg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fg/20"
          aria-label="Dismiss notification"
          @click="dismiss(toast.id)"
        >
          <svg
            class="h-3 w-3"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>
