Laravel Radar

{{ $total }} {{ $pluralizedVulnerability }} detected

Radar detected vulnerable dependencies that may expose this application to known exploits and should be reviewed.

Breakdown: {{ $counts['critical'] }} critical, {{ $counts['high'] }} high, {{ $counts['medium'] }} medium, {{ $counts['low'] }} low, {{ $counts['unknown'] }} unknown.

Affected packages:
@foreach ($vulnerabilities as $vulnerability)
- {{ $vulnerability->packageName }} {{ $vulnerability->installedVersion }} — {{ $vulnerability->severity->value }} severity ({{ $vulnerability->advisoryId }})
@endforeach
@if ($remainingCount > 0)
...and {{ $remainingCount }} more.
@endif
@if ($dashboardUrl !== null)

View in Radar: {{ $dashboardUrl }}
@endif

Regards,
Radar
