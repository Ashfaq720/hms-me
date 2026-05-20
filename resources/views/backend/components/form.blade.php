{{-- Form Component --}}
{{-- Single Responsibility: Provide consistent form structure with validation --}}
@props([
    'id' => null,
    'method' => 'POST',
    'action' => '',
    'enctype' => null,
    'fields' => [],
    'sections' => [],
    'submitButton' => ['label' => 'Save', 'variant' => 'primary', 'icon' => 'fas fa-save'],
    'cancelButton' => null,
    'showActions' => true
])

<form
    @if($id) id="{{ $id }}" @endif
    method="{{ $method }}"
    action="{{ $action }}"
    @if($enctype) enctype="{{ $enctype }}" @endif
>
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    @if(count($sections) > 0)
        @foreach($sections as $section)
            @if(isset($section['title']))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ $section['title'] }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($section['fields'] as $field)
                                @include('backend.components.form-field', ['field' => $field])
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    @foreach($section['fields'] as $field)
                        @include('backend.components.form-field', ['field' => $field])
                    @endforeach
                </div>
            @endif
        @endforeach
    @else
        <div class="row">
            @foreach($fields as $field)
                @include('backend.components.form-field', ['field' => $field])
            @endforeach
        </div>
    @endif

    @if($showActions)
        <div class="d-flex justify-content-end gap-2 mt-4">
            @if($cancelButton)
                <a href="{{ $cancelButton['url'] ?? '#' }}" class="btn btn-secondary">
                    @if(isset($cancelButton['icon']))
                        <i class="{{ $cancelButton['icon'] }} me-2"></i>
                    @endif
                    {{ $cancelButton['label'] ?? 'Cancel' }}
                </a>
            @endif
            <button type="submit" class="btn btn-{{ $submitButton['variant'] ?? 'primary' }}">
                @if(isset($submitButton['icon']))
                    <i class="{{ $submitButton['icon'] }} me-2"></i>
                @endif
                {{ $submitButton['label'] }}
            </button>
        </div>
    @endif
</form>