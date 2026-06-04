@props([
    'text' => '',
    'limit' => 100,
    'readMoreText' => 'common.read_more',
    'readLessText' => 'common.read_less',
    'options' => [],
])

@php
    // Default options
    $defaults = [
        'strip_tags' => true,
        'container_class' => 'text-wrapper',
        'short_class' => 'short-text',
        'full_class' => 'full-text d-none',
        'link_class' => 'read-more text-primary',
        'max_width' => '200px',
        'preserve_html' => false,
        'ellipsis' => '...',
        'force_truncate' => false,
    ];

    $options = array_merge($defaults, $options);

    // Clean text
    $cleanText = $options['strip_tags'] ? strip_tags($text) : $text;
    $originalText = $text;

    // Check truncation
    $needsTruncation = strlen($cleanText) > $limit || $options['force_truncate'];

    // Truncate
    $truncatedText = Str::limit($cleanText, $limit, $options['ellipsis']);
@endphp

@if ($needsTruncation)
    <div class="{{ $options['container_class'] }}" style="max-width: {{ $options['max_width'] }};">
        {{-- Short version --}}
        <span class="{{ $options['short_class'] }}">
            {{ $truncatedText }}
        </span>

        {{-- Full version --}}
        <span class="{{ $options['full_class'] }}">
            @if ($options['preserve_html'])
                {!! $originalText !!}
            @else
                {{ $cleanText }}
            @endif
        </span>

        {{-- Toggle link --}}
        <a href="javascript:void(0);" 
           class="{{ $options['link_class'] }}"
           data-read-more="{{ __($readMoreText) }}"
           data-read-less="{{ __($readLessText) }}">
           {{ __($readMoreText) }}
        </a>
    </div>
@else
    <div style="max-width: {{ $options['max_width'] }};">
        @if ($options['preserve_html'])
            {!! $originalText !!}
        @else
            {{ $cleanText }}
        @endif
    </div>
@endif