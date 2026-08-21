<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name', 'MENRO Tree Monitoring') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 600px) {
    .wrapper {
        padding: 0 !important;
    }
    .inner-body {
        width: 100% !important;
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
    }
    .footer {
        width: 100% !important;
    }
    .header {
        padding: 24px 20px 16px !important;
    }
    .content-cell {
        padding: 24px 20px 32px !important;
    }
    .otp-code {
        font-size: 28px !important;
        letter-spacing: 5px !important;
    }
}
@media only screen and (max-width: 500px) {
    .button-td {
        width: 100% !important;
        padding: 14px 20px !important;
        display: block !important;
        box-sizing: border-box !important;
    }
    .button {
        width: 100% !important;
        text-align: center !important;
        display: block !important;
    }
}
</style>
{!! $head ?? '' !!}
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f1f5f9; width: 100%; margin: 0; padding: 32px 0;">
<tr>
<td align="center" style="padding: 0 12px;">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; margin: 0 auto; width: 100%;">
<!-- Email Body Card -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: none;">
<table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" style="width: 600px; max-width: 600px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
<!-- Top Brand Accent Bar -->
<tr>
<td class="accent-bar" style="background: #059669; height: 6px; font-size: 0; line-height: 0;">&nbsp;</td>
</tr>
{!! $header ?? '' !!}
<!-- Body Content -->
<tr>
<td class="content-cell" style="padding: 32px 40px 40px;">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
