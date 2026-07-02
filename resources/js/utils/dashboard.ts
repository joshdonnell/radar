export function severityColor(severity: string): string {
  const map: Record<string, string> = {
    critical: 'bg-danger/10 text-danger ring-danger/30',
    high: 'bg-danger/10 text-danger ring-danger/25',
    medium: 'bg-warning/10 text-warning ring-warning/25',
    low: 'bg-success/10 text-success ring-success/25',
    unknown: 'bg-surface-2 text-muted ring-border-strong',
  }

  return map[severity.toLowerCase()] ?? map.unknown
}

export function updateColor(type: string): string {
  const map: Record<string, string> = {
    major: 'bg-danger/10 text-danger ring-danger/25',
    minor: 'bg-warning/10 text-warning ring-warning/25',
    patch: 'bg-success/10 text-success ring-success/25',
  }

  return map[type.toLowerCase()] ?? map.patch
}

export function relationColor(isDirect: boolean): string {
  return isDirect
    ? 'bg-success/10 text-success ring-success/25'
    : 'bg-surface-2 text-muted ring-border-strong'
}

export function parentUpdateCommand(ecosystem: string, parent: string): string {
  if (ecosystem === 'composer') {
    return `composer update ${parent} --with-dependencies`
  }

  return `npm update ${parent}`
}
