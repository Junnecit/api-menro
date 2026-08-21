@props(['url'])
<tr>
<td class="header" style="background-color: #ffffff; padding: 32px 40px 18px; border-bottom: 1px solid #f1f5f9;">
    <table cellpadding="0" cellspacing="0" border="0" role="presentation" width="100%">
        <tr>
            <td valign="middle" style="width: 48px; padding-right: 14px;">
                <a href="{{ $url }}" style="text-decoration: none; display: inline-block;">
                @if (trim($slot) === 'Laravel')
                    <img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo" height="42" style="display: block; max-height: 42px; width: auto; border: 0;">
                @else
                    {!! $slot !!}
                @endif
                </a>
            </td>
            <td valign="middle" style="border-left: 2px solid #e2e8f0; padding-left: 14px;">
                <a href="{{ $url }}" style="text-decoration: none; display: block; color: inherit;">
                    <div style="font-size: 15px; font-weight: 700; color: #0f172a; line-height: 1.25; letter-spacing: -0.01em;">
                        MENRO Tagoloan
                    </div>
                    <div style="font-size: 12px; font-weight: 600; color: #059669; line-height: 1.2; margin-top: 2px;">
                        Tree Planting Monitoring System
                    </div>
                </a>
            </td>
        </tr>
    </table>
</td>
</tr>
