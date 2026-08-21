@props([
    'url',
    'color' => 'primary',
    'align' => 'left',
])
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 28px 0; width: 100%;">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse: separate; margin: 0;">
<tr>
<td align="center" valign="middle" class="button-td button-td-{{ $color }}" style="border-radius: 9999px; background-color: #059669; background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 14px 32px; box-shadow: 0 4px 14px 0 rgba(5, 150, 105, 0.35); text-align: center;">
    <a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener" style="display: inline-block; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff !important; text-decoration: none; border-radius: 9999px; line-height: 1; text-align: center; letter-spacing: 0.01em; padding: 0; margin: 0; background: transparent; border: none;">
        <span style="color: #ffffff !important; text-decoration: none; font-weight: 700;">{!! $slot !!}</span>
    </a>
</td>
</tr>
</table>
</td>
</tr>
</table>
