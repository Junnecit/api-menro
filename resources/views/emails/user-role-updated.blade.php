<x-mail::message>
<div style="margin-bottom: 12px;">
    <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
        Role Updated
    </span>
</div>

# Account Role Updated

Hi **{{ $userName }}**,

Your account role on the **MENRO Tree Planting Monitoring System** has been updated by **{{ $actorName }}**.

<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin: 20px 0; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
    <tr>
        <td style="padding: 14px 18px; border-bottom: 1px solid #f1f5f9;">
            <div style="font-size: 13px; font-weight: 600; color: #0f172a;">Role Details:</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #475569;">
            <strong>Previous Role:</strong> {{ $oldRoleName }}
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #047857; font-weight: 700;">
            <strong>New Role:</strong> {{ $newRoleName }}
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 18px; font-size: 13px; color: #475569;">
            @if($newRoleSlug === 'monitor')
                &#10003;&nbsp;&nbsp;You now have access to inspect and monitor trees, view planting zones, and update tree health across your assigned area.
            @elseif($newRoleSlug === 'user')
                &#10003;&nbsp;&nbsp;You can record tree plantings, take field photos with the Pro Camera, and submit planting logs.
            @else
                &#10003;&nbsp;&nbsp;You have updated access rights as configured by the municipal administrator.
            @endif
        </td>
    </tr>
</table>

<x-mail::button :url="$frontendUrl" color="primary">
Open MENRO Portal &rarr;
</x-mail::button>

<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin: 24px 0 20px;">
    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.55;">
        <strong style="color: #334155;">Need assistance?</strong> If you have questions about this role change, please contact your municipal administrator or email us at <a href="mailto:{{ $supportEmail }}" style="color: #059669; font-weight: 600;">{{ $supportEmail }}</a>.
    </p>
</div>

Best regards,<br>
**MENRO Tagoloan Team**
</x-mail::message>
