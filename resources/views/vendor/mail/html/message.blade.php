@php
    $frontendUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/');
    $supportEmail = config('mail.from.address');
@endphp
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="$frontendUrl">
{{-- Replaced with a cid: reference by App\Mail\EmbedsMenroLogo before sending, so
     the logo renders in mail clients (Gmail, Outlook, etc.) that won't fetch a
     localhost/private-network image URL or render an inline base64 data: URI. --}}
<img src="MENRO_LOGO_CID" class="logo" alt="{{ config('app.name') }}">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
[Help](mailto:{{ $supportEmail }})&nbsp;&nbsp;&nbsp;[Terms & conditions]({{ $frontendUrl }})&nbsp;&nbsp;&nbsp;[Privacy Policy]({{ $frontendUrl }})
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
