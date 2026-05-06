export const RadarColor = {
  Cyan: 'cyan',
  Rose: 'rose',
  Amber: 'amber',
  Emerald: 'emerald',
  Slate: 'slate',
  White: 'white',
} as const

export type RadarColor = (typeof RadarColor)[keyof typeof RadarColor]

type ColorClasses = {
  bg: string
  text: string
  ring: string
}

const colorClasses: Record<RadarColor, ColorClasses> = {
  [RadarColor.Cyan]: {
    bg: 'bg-cyan-500/10',
    text: 'text-cyan-300',
    ring: 'ring-cyan-500/15',
  },
  [RadarColor.Rose]: {
    bg: 'bg-rose-500/10',
    text: 'text-rose-300',
    ring: 'ring-rose-500/15',
  },
  [RadarColor.Amber]: {
    bg: 'bg-amber-500/10',
    text: 'text-amber-300',
    ring: 'ring-amber-500/15',
  },
  [RadarColor.Emerald]: {
    bg: 'bg-emerald-500/10',
    text: 'text-emerald-300',
    ring: 'ring-emerald-500/15',
  },
  [RadarColor.Slate]: {
    bg: 'bg-slate-500/10',
    text: 'text-slate-400',
    ring: 'ring-slate-500/15',
  },
  [RadarColor.White]: {
    bg: 'bg-white/[0.04]',
    text: 'text-white',
    ring: 'ring-white/[0.08]',
  },
}

export function radarColorClasses(
  color: RadarColor = RadarColor.White,
): ColorClasses {
  return colorClasses[color]
}

export function radarColorClassList(
  color: RadarColor = RadarColor.White,
): string[] {
  const classes = radarColorClasses(color)

  return [classes.bg, classes.text, classes.ring]
}

export function radarFocusRingClass(
  color: RadarColor = RadarColor.Cyan,
): string {
  return {
    [RadarColor.Cyan]: 'focus-visible:ring-cyan-400/40',
    [RadarColor.Rose]: 'focus-visible:ring-rose-400/40',
    [RadarColor.Amber]: 'focus-visible:ring-amber-400/40',
    [RadarColor.Emerald]: 'focus-visible:ring-emerald-400/40',
    [RadarColor.Slate]: 'focus-visible:ring-slate-400/40',
    [RadarColor.White]: 'focus-visible:ring-white/40',
  }[color]
}
