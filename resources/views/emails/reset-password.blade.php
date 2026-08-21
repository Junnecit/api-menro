<x-mail::message>
<div style="margin-bottom: 12px;">
    <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
        Password Reset
    </span>
</div>

# Reset your password

Hi **{{ $userName ?? 'there' }}**,

We received a request to reset the password for your **MENRO Tree Planting Monitoring System** account. 

Click the button below to choose a new, secure password:

<x-mail::button :url="$actionUrl" color="primary">
Reset Password &rarr;
</x-mail::button>

<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin: 24px 0 20px;">
    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.55;">
        <strong style="color: #334155;">Security Notice:</strong> This password reset link is valid for <strong>{{ $expireMinutes }} minutes</strong>. If you did not request a password reset, no further action is required and your account remains completely secure.
    </p>
</div>

Best regards,<br>
**MENRO Tagoloan Team**

<x-slot:subcopy>
<div style="font-size: 12px; color: #64748b; line-height: 1.5;">
If you are having trouble clicking the "Reset Password" button, copy and paste the following URL into your web browser:<br>
<a href="{{ $actionUrl }}" style="color: #059669; word-break: break-all;">{{ $actionUrl }}</a>
</div>
</x-slot:subcopy>
</x-mail::message>
