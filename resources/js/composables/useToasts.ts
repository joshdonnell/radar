import { readonly, ref } from 'vue'

export type Toast = {
  id: number
  tone: 'error' | 'success'
  message: string
}

const toasts = ref<Toast[]>([])
let nextId = 1

export function useToasts() {
  const dismiss = (id: number) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  const push = (tone: Toast['tone'], message: string, duration = 6000) => {
    const id = nextId++

    toasts.value = [...toasts.value, { id, tone, message }]

    window.setTimeout(() => dismiss(id), duration)
  }

  return {
    toasts: readonly(toasts),
    dismiss,
    error: (message: string) => push('error', message, 10000),
    success: (message: string) => push('success', message),
  }
}
