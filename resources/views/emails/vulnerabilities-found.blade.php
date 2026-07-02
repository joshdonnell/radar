@php
    $severityStyles = [
        'critical' => ['ink' => '#e5484d', 'bg' => '#fef2f2', 'border' => '#fbdada', 'label' => '#c2373b', 'name' => 'Critical'],
        'high' => ['ink' => '#e5484d', 'bg' => '#fef2f2', 'border' => '#fbdada', 'label' => '#c2373b', 'name' => 'High'],
        'medium' => ['ink' => '#d9a316', 'bg' => '#fffbeb', 'border' => '#f6e6b8', 'label' => '#b58712', 'name' => 'Medium'],
        'low' => ['ink' => '#2f9e63', 'bg' => '#effaf3', 'border' => '#c9edd8', 'label' => '#2f9e63', 'name' => 'Low'],
        'unknown' => ['ink' => '#71717a', 'bg' => '#f4f4f5', 'border' => '#e4e4e8', 'label' => '#71717a', 'name' => 'Unknown'],
    ];

    $activeSeverities = array_filter(
        $severityStyles,
        fn ($style, $key) => ($counts[$key] ?? 0) > 0,
        ARRAY_FILTER_USE_BOTH,
    );

    $mono = "Consolas,Menlo,Monaco,'Courier New',monospace";
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;color:#18181b;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">
    <div style="display:none;max-height:0;overflow:hidden;color:transparent;opacity:0;">
        Radar found {{ $total }} {{ $pluralizedVulnerability }} in your project dependencies.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f4f5;padding:48px 20px 72px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e8e8ec;border-radius:16px;overflow:hidden;">
                    {{-- Header --}}
                    <tr>
                        <td style="padding:30px 32px 26px;border-bottom:1px solid #f1f1f4;">
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 22px;">
                                <tr>
                                    <td style="width:30px;height:30px;background:#f4f4f5;border:1px solid #e4e4e8;border-radius:8px;text-align:center;vertical-align:middle;font-size:17px;line-height:1;color:#18181b;">&#9678;</td>
                                    <td style="padding-left:11px;font-size:15px;font-weight:600;color:#18181b;">Laravel Radar</td>
                                </tr>
                            </table>
                            <h1 style="margin:0 0 10px;font-size:24px;font-weight:700;letter-spacing:-0.02em;color:#18181b;">{{ $total }} {{ $pluralizedVulnerability }} detected</h1>
                            <p style="margin:0;color:#52525b;font-size:14.5px;line-height:1.55;">A scan of your project dependencies has identified packages requiring attention.</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:26px 32px 32px;">
                            {{-- Severity summary --}}
                            @if (count($activeSeverities) > 0)
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 28px;border-collapse:separate;border-spacing:14px 0;">
                                    @foreach (array_chunk($activeSeverities, 2, true) as $row)
                                        <tr>
                                            @foreach ($row as $key => $style)
                                                <td width="50%" style="background:{{ $style['bg'] }};border:1px solid {{ $style['border'] }};border-radius:12px;padding:16px 18px;">
                                                    <div style="font-size:30px;font-weight:700;line-height:1;letter-spacing:-0.02em;color:{{ $style['ink'] }};">{{ $counts[$key] }}</div>
                                                    <div style="margin-top:9px;font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:{{ $style['label'] }};">{{ $style['name'] }} severity</div>
                                                </td>
                                            @endforeach
                                            @if (count($row) === 1)
                                                <td width="50%"></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <div style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#a1a1aa;margin-bottom:14px;">Affected packages</div>

                            @foreach ($vulnerabilities as $vulnerability)
                                @php $style = $severityStyles[$vulnerability->severity->value] ?? $severityStyles['unknown']; @endphp
                                <div style="border:1px solid #eaeaee;border-radius:12px;padding:18px;margin-bottom:14px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="vertical-align:top;">
                                                <span style="font-family:{{ $mono }};font-weight:600;font-size:13.5px;color:#18181b;">{{ $vulnerability->packageName }}</span>
                                                &nbsp;
                                                <span style="font-family:{{ $mono }};font-size:11.5px;color:#e5484d;background:#fef2f2;border:1px solid #fbdada;padding:2px 7px;border-radius:5px;white-space:nowrap;">{{ $vulnerability->installedVersion }}</span>
                                                @if ($vulnerability->patchedVersion)
                                                    <span style="color:#b4b4bb;">&nbsp;&rarr;&nbsp;</span>
                                                    <span style="font-family:{{ $mono }};font-size:11.5px;color:#2f9e63;background:#effaf3;border:1px solid #c9edd8;padding:2px 7px;border-radius:5px;white-space:nowrap;">{{ $vulnerability->patchedVersion }}</span>
                                                @endif
                                            </td>
                                            <td align="right" style="vertical-align:top;white-space:nowrap;">
                                                <span style="font-size:10.5px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:{{ $style['label'] }};background:{{ $style['bg'] }};border:1px solid {{ $style['border'] }};padding:3px 9px;border-radius:6px;">{{ $vulnerability->severity->value }}</span>
                                            </td>
                                        </tr>
                                    </table>

                                    @if ($vulnerability->cve)
                                        <div style="font-family:{{ $mono }};font-size:12px;color:#71717a;margin-top:12px;">{{ $vulnerability->cve }}</div>
                                    @endif

                                    @if ($vulnerability->suggestedCommand)
                                        <div style="margin-top:14px;background:#fafafa;border:1px solid #eeeef1;border-radius:9px;padding:12px 14px;">
                                            <div style="font-size:11px;color:#a1a1aa;margin-bottom:6px;">Recommended Action</div>
                                            <code style="font-family:{{ $mono }};font-size:12px;color:#3f3f46;word-break:break-all;">{{ $vulnerability->suggestedCommand }}</code>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if ($remainingCount > 0)
                                <p style="margin:4px 0 0;color:#71717a;font-size:13px;line-height:1.6;">...and {{ $remainingCount }} more.</p>
                            @endif

                            @if ($dashboardUrl !== null)
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:24px;">
                                    <tr>
                                        <td style="border-radius:10px;background:#0a0a0b;">
                                            <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:11px 18px;color:#fafafa;font-size:13.5px;font-weight:600;text-decoration:none;">View in Radar</a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:26px 0 0;color:#52525b;font-size:13.5px;line-height:1.6;border-top:1px solid #f1f1f4;padding-top:22px;">To resolve these issues, apply the recommended updates in your local environment, run your test suite, and deploy the updated lockfile.</p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:22px 32px 28px;border-top:1px solid #f1f1f4;text-align:center;">
                            <p style="margin:0;color:#a1a1aa;font-size:11.5px;line-height:1.6;">This is an automated security alert from Laravel Radar.<br>Radar never modifies your dependencies automatically.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
