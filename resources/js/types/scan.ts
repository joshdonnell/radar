export interface Scan {
  id: string
  score: number | null
  package_count: number
  vulnerability_count: number
  created_at: string | null
  created_at_human: string | null
}
