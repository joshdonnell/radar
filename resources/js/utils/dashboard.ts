export function severityColor(severity: string): string {
  const map: Record<string, string> = {
    critical: 'bg-rose-500/10 text-rose-300 ring-rose-500/20',
    high: 'bg-orange-500/10 text-orange-300 ring-orange-500/20',
    medium: 'bg-amber-500/10 text-amber-300 ring-amber-500/20',
    low: 'bg-blue-500/10 text-blue-300 ring-blue-500/20',
    unknown: 'bg-slate-500/10 text-slate-300 ring-slate-500/20',
  }

  return map[severity.toLowerCase()] ?? map.unknown
}

export function updateColor(type: string): string {
  const map: Record<string, string> = {
    major: 'bg-rose-500/10 text-rose-300 ring-rose-500/20',
    minor: 'bg-amber-500/10 text-amber-300 ring-amber-500/20',
    patch: 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/20',
  }

  return map[type.toLowerCase()] ?? map.patch
}

export function parentUpdateCommand(ecosystem: string, parent: string): string {
  if (ecosystem === 'composer') {
    return `composer update ${parent} --with-dependencies`
  }

  return `npm update ${parent}`
}
