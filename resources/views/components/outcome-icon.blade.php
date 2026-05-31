@props(['status' => 'waiting', 'size' => 14])
@php($s = (int) $size)
@switch($status)
    @case('waiting')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('rejected')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('interview')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.5-1.632z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('success')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @default
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
@endswitch
