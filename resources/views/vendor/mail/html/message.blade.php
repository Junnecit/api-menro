@php
    $frontendUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/');
    $supportEmail = config('mail.from.address');
@endphp
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="$frontendUrl">
{{-- Replaced with a cid: reference by App\Mail\EmbedsMenroLogo before sending --}}
<img src="MENRO_LOGO_CID" class="logo" alt="{{ config('app.name', 'MENRO Tagoloan') }}" style="max-height: 42px; width: auto; display: block; border: 0;">
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
<x-mail::footer />
</x-slot:footer>
</x-mail::layout>
