{{-- Page Header Component --}}
{{-- Single Responsibility: Display consistent page headers with breadcrumbs and actions --}}
@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumbs' => [],
    'actions' => []
])

<div class="py-5">
    <div class="row g-4 align-items-center">
        <div class="col">
            @if(!empty($breadcrumbs))
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        @foreach($breadcrumbs as $breadcrumb)
                            @if($loop->last)
                                <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['title'] }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ $breadcrumb['url'] ?? '#' }}">{{ $breadcrumb['title'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            @endif
            <h1 class="h3 m-0">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-muted mt-1 mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if(!empty($actions))
            <div class="col-auto">
                <div class="d-flex gap-2">
                    @foreach($actions as $action)
                        @if(isset($action['type']) && $action['type'] === 'button')
                            <button
                                type="{{ $action['button_type'] ?? 'button' }}"
                                class="btn btn-{{ $action['variant'] ?? 'primary' }} {{ $action['class'] ?? '' }}"
                                @if(isset($action['attributes']))
                                    @foreach($action['attributes'] as $key => $value)
                                        {{ $key }}="{{ $value }}"
                                    @endforeach
                                @endif
                            >
                                @if(isset($action['icon']))
                                    <i class="{{ $action['icon'] }} me-2"></i>
                                @endif
                                {{ $action['label'] ?? '' }}
                            </button>
                        @else
                            <a
                                href="{{ $action['url'] ?? '#' }}"
                                class="btn btn-{{ $action['variant'] ?? 'primary' }} {{ $action['class'] ?? '' }}"
                                @if(isset($action['attributes']))
                                    @foreach($action['attributes'] as $key => $value)
                                        {{ $key }}="{{ $value }}"
                                    @endforeach
                                @endif
                            >
                                @if(isset($action['icon']))
                                    <i class="{{ $action['icon'] }} me-2"></i>
                                @endif
                                {{ $action['label'] ?? '' }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>