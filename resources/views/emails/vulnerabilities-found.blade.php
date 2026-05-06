<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;color:transparent;opacity:0;">
        Radar found {{ $total }} {{ $pluralizedVulnerability }} in your project dependencies.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;">
                    <tr>
                        <td style="padding:24px 28px;border-top:4px solid #22d3ee;border-bottom:1px solid #e2e8f0;">
                            <p style="margin:0 0 12px;color:#0f172a;font-size:14px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">Laravel Radar</p>
                            <h1 style="margin:0;color:#0f172a;font-size:24px;line-height:1.3;font-weight:700;">{{ $total }} {{ $pluralizedVulnerability }} detected</h1>
                            <p style="margin:12px 0 0;color:#475569;font-size:15px;line-height:1.6;">Radar detected vulnerable dependencies that may expose this application to known exploits and should be reviewed.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px;border-collapse:collapse;">
                                <tr>
                                    <td style="padding:0 0 8px;color:#0f172a;font-size:16px;font-weight:700;">Severity breakdown</td>
                                </tr>
                                <tr>
                                    <td style="padding:0;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                            @foreach (['critical' => '#e11d48', 'high' => '#ea580c', 'medium' => '#ca8a04', 'low' => '#2563eb', 'unknown' => '#64748b'] as $severity => $color)
                                                <tr>
                                                    <td style="padding:6px 0;border-bottom:1px solid #f1f5f9;color:#475569;font-size:14px;text-transform:capitalize;">{{ $severity }}</td>
                                                    <td align="right" style="padding:6px 0;border-bottom:1px solid #f1f5f9;color:{{ $color }};font-size:14px;font-weight:700;">{{ $counts[$severity] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <h2 style="margin:0 0 12px;color:#0f172a;font-size:16px;line-height:1.4;">Affected packages</h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                                @foreach ($vulnerabilities as $vulnerability)
                                    <tr>
                                        <td style="padding:14px 16px;border-bottom:1px solid #e2e8f0;">
                                            <p style="margin:0;color:#0f172a;font-size:15px;font-weight:700;">{{ $vulnerability->packageName }}</p>
                                            <p style="margin:4px 0 0;color:#64748b;font-size:13px;line-height:1.5;">
                                                {{ $vulnerability->installedVersion }} · {{ $vulnerability->advisoryId }} · {{ $vulnerability->severity->value }} severity
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            @if ($remainingCount > 0)
                                <p style="margin:12px 0 0;color:#475569;font-size:14px;line-height:1.6;">...and {{ $remainingCount }} more.</p>
                            @endif

                            @if ($dashboardUrl !== null)
                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:24px;">
                                    <tr>
                                        <td style="border-radius:8px;background:#0f172a;">
                                            <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:11px 16px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">View in Radar</a>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px;border-top:1px solid #e2e8f0;color:#64748b;font-size:13px;line-height:1.6;">
                            Regards,<br>
                            Radar
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
