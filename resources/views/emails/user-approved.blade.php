<x-mail::message>
<div style="margin-bottom: 12px;">
    <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
        Account Approved
    </span>
</div>

# You're all set!

Hi **{{ $userName }}**,

Great news! Your account for the **MENRO Tree Planting Monitoring System** has been reviewed and approved by the administrator.

You now have full access to our environmental management platform.

<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin: 20px 0; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
    <tr>
        <td style="padding: 14px 18px; border-bottom: 1px solid #f1f5f9;">
            <div style="font-size: 13px; font-weight: 600; color: #0f172a;">What you can do now:</div>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #475569;">
            &#10003;&nbsp;&nbsp;Submit and manage tree planting requests
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #475569;">
            &#10003;&nbsp;&nbsp;Monitor tree health, coordinates, and planting progress
        </td>
    </tr>
    <tr>
        <td style="padding: 10px 18px; font-size: 13px; color: #475569;">
            &#10003;&nbsp;&nbsp;Access environmental reports and municipal analytics
        </td>
    </tr>
</table>

<x-mail::button :url="$frontendUrl" color="primary">
Log In to Portal &rarr;
</x-mail::button>

<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin: 24px 0 20px;">
    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.55;">
        <strong style="color: #334155;">Need assistance?</strong> If you have any questions or require support, please reach out to our team at <a href="mailto:{{ $supportEmail }}" style="color: #059669; font-weight: 600;">{{ $supportEmail }}</a>.
    </p>
</div>

Best regards,<br>
**MENRO Tagoloan Team**
</x-mail::message>
