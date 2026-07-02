export const RadarColor = {
  Danger: 'danger',
  Warning: 'warning',
  Success: 'success',
  Abandoned: 'abandoned',
  Neutral: 'neutral',
  Fg: 'fg',
} as const

export type RadarColor = (typeof RadarColor)[keyof typeof RadarColor]

type ColorClasses = {
  bg: string
  text: string
  ring: string
}

const colorClasses: Record<RadarColor, ColorClasses> = {
  [RadarColor.Danger]: {
    bg: 'bg-danger/10',
    text: 'text-danger',
    ring: 'ring-danger/25',
  },
  [RadarColor.Warning]: {
    bg: 'bg-warning/10',
    text: 'text-warning',
    ring: 'ring-warning/25',
  },
  [RadarColor.Success]: {
    bg: 'bg-success/10',
    text: 'text-success',
    ring: 'ring-success/25',
  },
  [RadarColor.Abandoned]: {
    bg: 'bg-abandoned/10',
    text: 'text-abandoned',
    ring: 'ring-abandoned/25',
  },
  [RadarColor.Neutral]: {
    bg: 'bg-surface-2',
    text: 'text-muted',
    ring: 'ring-border-strong',
  },
  [RadarColor.Fg]: {
    bg: 'bg-fg/[0.04]',
    text: 'text-fg',
    ring: 'ring-border',
  },
}

export function radarColorClasses(
  color: RadarColor = RadarColor.Fg,
): ColorClasses {
  return colorClasses[color]
}

export function radarColorClassList(
  color: RadarColor = RadarColor.Fg,
): string[] {
  const classes = radarColorClasses(color)

  return [classes.bg, classes.text, classes.ring]
}
