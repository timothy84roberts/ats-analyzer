@props(['slug' => 'unknown', 'size' => 14])
@php($s = (int) $size)
@switch($slug)
    @case('resume_submitted')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14 3v5h5M9 13h6M9 17h6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('skill_test')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M9 4H7a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9 4a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2 2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2z" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9 14l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('recruiter_interview')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('technical_interview')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M8 17l-5-5 5-5M16 7l5 5-5 5M13 4l-2 16" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('executive_hr_interview')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('offer')
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4z" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M5 4H3v1a3 3 0 0 0 3 3M19 4h2v1a3 3 0 0 1-3 3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @default
        <svg {{ $attributes->merge(['style' => 'flex-shrink:0;']) }} width="{{ $s }}" height="{{ $s }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 8v4l3 2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
@endswitch
