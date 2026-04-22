@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'bg-green-500/20 border border-green-400/30 text-green-100 px-4 py-3 rounded-lg text-sm flex items-center gap-2']) }}>
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ $status }}</span>
    </div>
@endif
