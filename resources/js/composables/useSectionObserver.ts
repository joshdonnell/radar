import { useIntersectionObserver, useScroll } from '@vueuse/core'
import { computed, ref, watchEffect } from 'vue'

export function useSectionObserver(
  getSections: () => (HTMLElement | null)[],
  initialSection = '',
) {
  const activeSection = ref(initialSection)
  const visibleSections = ref(new Set<string>())
  const { arrivedState } = useScroll(window)

  const sections = computed(() =>
    getSections().filter((section): section is HTMLElement => section !== null),
  )

  useIntersectionObserver(
    sections,
    (entries) => {
      const next = new Set(visibleSections.value)

      for (const entry of entries) {
        if (!(entry.target instanceof HTMLElement)) continue

        const id = entry.target.dataset.sectionId ?? ''

        if (entry.isIntersecting) {
          next.add(id)
        } else {
          next.delete(id)
        }
      }

      visibleSections.value = next
    },
    {
      rootMargin: '-15% 0px -70% 0px',
      threshold: 0,
    },
  )

  // The last section is often too short to reach the observed band, so it
  // becomes active once the page cannot scroll any further. Between sections
  // the previously active one stays highlighted.
  watchEffect(() => {
    const ids = sections.value.map((section) => section.dataset.sectionId ?? '')

    if (arrivedState.bottom && window.scrollY > 0 && ids.length) {
      activeSection.value = ids[ids.length - 1]

      return
    }

    const topmostVisible = ids.find((id) => visibleSections.value.has(id))

    if (topmostVisible) {
      activeSection.value = topmostVisible
    }
  })

  return { activeSection }
}
