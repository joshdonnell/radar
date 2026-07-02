import { useIntersectionObserver } from '@vueuse/core'
import { computed, ref } from 'vue'

export function useSectionObserver(
  getSections: () => (HTMLElement | null)[],
  initialSection = '',
) {
  const activeSection = ref(initialSection)

  const sections = computed(() =>
    getSections().filter((section): section is HTMLElement => section !== null),
  )

  useIntersectionObserver(
    sections,
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return
        if (!(entry.target instanceof HTMLElement)) return

        activeSection.value = entry.target.dataset.sectionId ?? ''
      })
    },
    {
      rootMargin: '-15% 0px -70% 0px',
      threshold: 0,
    },
  )

  return { activeSection }
}
