{{-- Modal Component --}}
{{-- Single Responsibility: Provide consistent modal structure --}}
@props([
    'id' => 'modal',
    'title' => '',
    'body' => '',
    'size' => 'md', // sm, md, lg, xl, fullscreen
    'centered' => false,
    'scrollable' => false,
    'showCloseButton' => true,
    'showFooter' => true,
    'footerActions' => [],
    'footerButtons' => [], // Backward compatibility
    'actions' => [] // Another format used in reports
])

{{-- Handle backward compatibility --}}
@php
    $actions = !empty($footerActions) ? $footerActions : (!empty($footerButtons) ? $footerButtons : (!empty($actions) ? $actions : []));
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" @if($centered) data-bs-backdrop="static" @endif>
    <div class="modal-dialog modal-{{ $size }} @if($centered) modal-dialog-centered @endif @if($scrollable) modal-dialog-scrollable @endif">
        <div class="modal-content">
            @if($title || $showCloseButton)
                <div class="modal-header">
                    @if($title)
                        <h5 class="modal-title">{{ $title }}</h5>
                    @endif
                    @if($showCloseButton)
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    @endif
                </div>
            @endif

            <div class="modal-body">
                @if($body)
                    @if(str_contains($body, '.'))
                        @include($body)
                    @else
                        {!! $body !!}
                    @endif
                @else
                    {{ $slot }}
                @endif
            </div>

            @if($showFooter && !empty($actions))
                <div class="modal-footer">
                    @foreach($actions as $action)
                        @if(isset($action['type']) && $action['type'] === 'button')
                            <button
                                type="{{ $action['button_type'] ?? 'button' }}"
                                class="btn btn-{{ $action['variant'] ?? 'secondary' }}"
                                @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                @if(isset($action['id'])) id="{{ $action['id'] }}" @endif
                                @if(isset($action['dismiss']) && $action['dismiss']) data-bs-dismiss="modal" @endif
                                @if(isset($action['attributes']))
                                    @foreach($action['attributes'] as $key => $value)
                                        {{ $key }}="{{ $value }}"
                                    @endforeach
                                @endif
                            >
                                @if(isset($action['icon']))
                                    <i class="{{ $action['icon'] }} me-2"></i>
                                @endif
                                {{ $action['label'] }}
                            </button>
                        @elseif(isset($action['type']) && $action['type'] === 'link')
                            <a
                                href="{{ $action['url'] }}"
                                class="btn btn-{{ $action['variant'] ?? 'primary' }}"
                                @if(isset($action['attributes']))
                                    @foreach($action['attributes'] as $key => $value)
                                        {{ $key }}="{{ $value }}"
                                    @endforeach
                                @endif
                            >
                                @if(isset($action['icon']))
                                    <i class="{{ $action['icon'] }} me-2"></i>
                                @endif
                                {{ $action['label'] }}
                            </a>
                        @else
                            {{-- Simple button format for backward compatibility --}}
                            <button
                                type="button"
                                class="btn btn-{{ $action['variant'] ?? 'secondary' }}"
                                @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                @if(isset($action['action']) && $action['action'] === 'close') data-bs-dismiss="modal" @endif
                                @if(isset($action['id'])) id="{{ $action['id'] }}" @endif
                                @if(isset($action['dismiss']) && $action['dismiss']) data-bs-dismiss="modal" @endif
                            >
                                {{ $action['label'] }}
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>