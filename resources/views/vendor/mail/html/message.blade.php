<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
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
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
