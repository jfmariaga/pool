@props(['click', 'href', 'color', 'classextra'])

<a href="{{ $href ?? 'javascript:' }}" x-on:click="{{ $click ?? '' }}" class=" border_none btn btn-sm grey btn-outline-{{ $color ?? 'secondary' }} {{ $classextra ?? '' }}" style="padding: 3px;"> 
    {{ $slot }}
</a>