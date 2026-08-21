@php
    $frontendUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/');
    $supportEmail = config('mail.from.address');
@endphp
<tr>
<td>
<table class="footer" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="width: 600px; max-width: 600px; margin: 0 auto; text-align: center;">
<tr>
<td class="content-cell" align="center" style="padding: 28px 20px 16px; text-align: center;">
    <p style="font-size: 13px; font-weight: 600; color: #475569; margin: 0 0 6px; text-align: center;">
        MENRO Tree Planting Monitoring System
    </p>
    <p style="font-size: 12px; color: #64748b; margin: 0 0 12px; line-height: 1.5; text-align: center;">
        Municipal Environment and Natural Resources Office<br>
        Tagoloan, Misamis Oriental, Philippines
    </p>
    <p style="font-size: 11px; color: #94a3b8; margin: 0 0 14px; text-align: center;">
        This is an automated administrative notification. Please do not reply directly to this email.
    </p>
    <div style="font-size: 12px; color: #64748b; margin-top: 10px; text-align: center;">
        <a href="mailto:{{ $supportEmail }}" style="color: #059669; text-decoration: none; font-weight: 500;">Support</a>
        <span style="color: #cbd5e1; margin: 0 8px;">&bull;</span>
        <a href="{{ $frontendUrl }}" style="color: #059669; text-decoration: none; font-weight: 500;">Access Portal</a>
    </div>
</td>
</tr>
</table>
</td>
</tr>
