{{-- Form Field Component --}}
{{-- Single Responsibility: Render individual form fields --}}
@props(['field'])

<div class="col-{{ $field['col'] ?? 'md-12' }} mb-3">
    @if (
        $field['type'] === 'text' ||
            $field['type'] === 'email' ||
            $field['type'] === 'password' ||
            $field['type'] === 'number' ||
            $field['type'] === 'date' ||
            $field['type'] === 'time' ||
            $field['type'] === 'url' ||
            $field['type'] === 'tel')
        <label for="{{ $field['name'] }}" class="form-label">
            {{ $field['label'] }}
            @if (isset($field['required']) && $field['required'])
                <span class="text-danger">*</span>
            @endif
        </label>
        <input type="{{ $field['type'] }}" class="form-control @error($field['name']) is-invalid @enderror"
            id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ old($field['name'], $field['value'] ?? '') }}"
            @if (isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
            @if (isset($field['required']) && $field['required']) required @endif
            @if (isset($field['min'])) min="{{ $field['min'] }}" @endif
            @if (isset($field['max'])) max="{{ $field['max'] }}" @endif
            @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
            @if (isset($field['attributes'])) @foreach ($field['attributes'] as $key => $value)
                    {{ $key }}="{{ $value }}"
                @endforeach @endif />
        @error($field['name'])
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if (isset($field['help']))
            <div class="form-text">{{ $field['help'] }}</div>
        @endif
    @elseif($field['type'] === 'textarea')
        <label for="{{ $field['name'] }}" class="form-label">
            {{ $field['label'] }}
            @if (isset($field['required']) && $field['required'])
                <span class="text-danger">*</span>
            @endif
        </label>
        <textarea class="form-control @error($field['name']) is-invalid @enderror" id="{{ $field['name'] }}"
            name="{{ $field['name'] }}" rows="{{ $field['rows'] ?? 3 }}"
            @if (isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
            @if (isset($field['required']) && $field['required']) required @endif
            @if (isset($field['attributes'])) @foreach ($field['attributes'] as $key => $value)
                    {{ $key }}="{{ $value }}"
                @endforeach @endif>{{ old($field['name'], $field['value'] ?? '') }}</textarea>
        @error($field['name'])
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if (isset($field['help']))
            <div class="form-text">{{ $field['help'] }}</div>
        @endif
    @elseif($field['type'] === 'select')
        <label for="{{ $field['name'] }}" class="form-label">
            {{ $field['label'] }}
            @if (isset($field['required']) && $field['required'])
                <span class="text-danger">*</span>
            @endif
        </label>
        @php
            $isMultiple = false;
            if (isset($field['attributes']) && array_key_exists('multiple', $field['attributes'])) {
                $isMultiple = true;
            }
            if (isset($field['multiple']) && $field['multiple']) {
                $isMultiple = true;
            }
            $selectName = $field['name'] . ($isMultiple ? '[]' : '');
        @endphp
            <select class="form-select select2 @error($field['name']) is-invalid @enderror" id="{{ $field['name'] }}"
                name="{{ $selectName }}" @if (isset($field['required']) && $field['required']) required @endif
                @if(isset($field['placeholder'])) data-placeholder="{{ $field['placeholder'] }}" @endif
                @if (isset($field['attributes'])) @foreach ($field['attributes'] as $key => $value)
                        {{ $key }}="{{ $value }}"
                    @endforeach @endif
                @if (isset($field['data_attributes'])) @foreach ($field['data_attributes'] as $key => $value)
                        data-{{ $key }}="{{ $value }}"
                    @endforeach @endif>
            @if (isset($field['placeholder']))
                <option value="">{{ $field['placeholder'] }}</option>
            @endif
            @foreach ($field['options'] ?? [] as $value => $label)
                @php
                    $old = old($field['name'], $field['value'] ?? null);
                    $selected = false;
                    if ($isMultiple) {
                        if (is_array($old) && in_array($value, $old)) $selected = true;
                        if (!is_array($old) && $old == $value) $selected = true; // support single post
                    } else {
                        $selected = ($old == $value);
                    }
                @endphp
                <option value="{{ $value }}" {{ $selected ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error($field['name'])
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if (isset($field['help']))
            <div class="form-text">{{ $field['help'] }}</div>
        @endif
    @elseif($field['type'] === 'custom_select')
        <label for="{{ $field['name'] }}" class="form-label">
            {{ $field['label'] }}
            @if (isset($field['required']) && $field['required'])
                <span class="text-danger">*</span>
            @endif
        </label>
        @php
            $isCustomMultiple = false;
            if (isset($field['attributes']) && array_key_exists('multiple', $field['attributes'])) {
                $isCustomMultiple = true;
            }
            if (isset($field['multiple']) && $field['multiple']) {
                $isCustomMultiple = true;
            }
            $selectCustomName = $field['name'] . ($isCustomMultiple ? '[]' : '');
        @endphp
        <select class="form-select select2 @error($field['name']) is-invalid @enderror" id="{{ $field['name'] }}"
            name="{{ $selectCustomName }}" @if (isset($field['required']) && $field['required']) required @endif
            @if(isset($field['placeholder'])) data-placeholder="{{ $field['placeholder'] }}" @endif
            @if (isset($field['attributes'])) @foreach ($field['attributes'] as $key => $value)
                    {{ $key }}="{{ $value }}"
                @endforeach @endif
            @if (isset($field['data_attributes'])) @foreach ($field['data_attributes'] as $key => $value)
                    data-{{ $key }}="{{ $value }}"
                @endforeach @endif>
            @if (isset($field['placeholder']))
                <option value="">{{ $field['placeholder'] }}</option>
            @endif
            @foreach ($field['custom_options'] ?? [] as $option)
                @php
                    $patientData = [
                        'id' => $option->id ?? '',
                        'patient_id' => $option->patient_id ?? '',
                        'full_name' => $option->full_name ?? '',
                        'phone' => $option->phone ?? '',
                        'email' => $option->email ?? '',
                        'date_of_birth' => $option->date_of_birth ? format_date($option->date_of_birth) : ''
                    ];
                @endphp
                @php
                    $oldCustom = old($field['name'], $field['value'] ?? null);
                    $selectedCustom = false;
                    if ($isCustomMultiple) {
                        if (is_array($oldCustom) && in_array($option->id, $oldCustom)) $selectedCustom = true;
                        if (!is_array($oldCustom) && $oldCustom == $option->id) $selectedCustom = true;
                    } else {
                        $selectedCustom = ($oldCustom == $option->id);
                    }
                @endphp
                <option value="{{ $option->id ?? '' }}"
                    data-patient="{{ base64_encode(json_encode($patientData)) }}"
                    {{ $selectedCustom ? 'selected' : '' }}>
                    {{ $option->full_name ?? 'Unknown' }} ({{ $option->patient_id ?? 'N/A' }})
                </option>
            @endforeach
        </select>
        @error($field['name'])
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if (isset($field['help']))
            <div class="form-text">{{ $field['help'] }}</div>
        @endif
    @elseif($field['type'] === 'checkbox')
        <div class="form-check">
            <input type="checkbox" class="form-check-input @error($field['name']) is-invalid @enderror"
                id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $field['checkbox_value'] ?? 1 }}"
                {{ old($field['name'], $field['value'] ?? false) ? 'checked' : '' }}
                @if (isset($field['attributes'])) @foreach ($field['attributes'] as $key => $value)
                        {{ $key }}="{{ $value }}"
                    @endforeach @endif />
            <label class="form-check-label" for="{{ $field['name'] }}">
                {{ $field['label'] }}
                @if (isset($field['required']) && $field['required'])
                    <span class="text-danger">*</span>
                @endif
            </label>
            @error($field['name'])
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if (isset($field['help']))
                <div class="form-text">{{ $field['help'] }}</div>
            @endif
        </div>
    @elseif($field['type'] === 'radio')
        <label class="form-label">{{ $field['label'] }}</label>
        @foreach ($field['options'] ?? [] as $value => $label)
            <div class="form-check">
                <input type="radio" class="form-check-input @error($field['name']) is-invalid @enderror"
                    id="{{ $field['name'] }}_{{ $value }}" name="{{ $field['name'] }}"
                    value="{{ $value }}"
                    {{ old($field['name'], $field['value'] ?? '') == $value ? 'checked' : '' }}
                    @if (isset($field['required']) && $field['required']) required @endif />
                <label class="form-check-label" for="{{ $field['name'] }}_{{ $value }}">
                    {{ $label }}
                </label>
            </div>
        @endforeach
        @error($field['name'])
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if (isset($field['help']))
            <div class="form-text">{{ $field['help'] }}</div>
        @endif
    @elseif($field['type'] === 'file')
        <label for="{{ $field['name'] }}" class="form-label">
            {{ $field['label'] }}
            @if (isset($field['required']) && $field['required'])
                <span class="text-danger">*</span>
            @endif
        </label>
        <input type="file" class="form-control @error($field['name']) is-invalid @enderror"
            id="{{ $field['name'] }}" name="{{ $field['name'] }}"
            @if (isset($field['accept'])) accept="{{ $field['accept'] }}" @endif
            @if (isset($field['required']) && $field['required']) required @endif
            @if (isset($field['attributes'])) @foreach ($field['attributes'] as $key => $value)
                    {{ $key }}="{{ $value }}"
                @endforeach @endif />
        @error($field['name'])
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if (isset($field['help']))
            <div class="form-text">{{ $field['help'] }}</div>
        @endif
    @endif
</div>
