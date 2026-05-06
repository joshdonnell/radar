import { useIntersectionObserver } from '@vueuse/core'
import { computed, ref } from 'vue'

type SectionElement = {
  readonly value: HTMLElement | null
}

export function useSectionObserver(
  sectionElements: Record<string, SectionElement>,
) {
  const sectionEntries = Object.entries(sectionElements)
  const activeSection = ref(sectionEntries[0]?.[0] ?? '')

  const sections = computed(() =>
    sectionEntries
      .map(([, sectionElement]) => sectionElement.value)
      .filter((section): section is HTMLElement => section !== null),
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
