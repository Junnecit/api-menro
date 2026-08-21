<x-mail::message>
<div style="margin-bottom: 12px;">
    <span style="display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
        Email Verification
    </span>
</div>

# Verify your email address

Hi **{{ $userName }}**,

Thank you for registering with the **MENRO Tree Planting Monitoring System**. Please use the 6-digit verification code below to verify your email address and activate your account.

<div style="background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%); background-color: #ecfdf5; border: 2px dashed #86efac; border-radius: 14px; padding: 26px 20px; text-align: center; margin: 26px 0;">
    <div style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">
        Verification Code
    </div>
    <div style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace; font-size: 38px; font-weight: 800; color: #065f46; letter-spacing: 10px; padding-left: 10px; line-height: 1.2;">
        {{ $code }}
    </div>
    <div style="font-size: 12px; color: #059669; font-weight: 600; margin-top: 10px;">
        &#9201; Expires in 10 minutes
    </div>
</div>

<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin: 22px 0 20px;">
    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.55;">
        <strong style="color: #334155;">Security Notice:</strong> Never share this code with anyone. MENRO administrators will never ask for your verification code. If you did not initiate this request, you can safely ignore this email.
    </p>
</div>

Best regards,<br>
**MENRO Tagoloan Team**
</x-mail::message>
