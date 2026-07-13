@extends('backend.layouts.master')

@section('title', 'Setting Details')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Setting Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('settings.index') }}">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $setting->key }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('settings.edit', $setting) }}" class="btn btn-primary">
                    <i class="fi fi-rr-edit me-1"></i> Edit Setting
                </a>
                <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                    <i class="fi fi-rr-arrow-left me-1"></i> Back to Settings
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Setting Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Setting Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-3 rounded bg-primary bg-opacity-10">
                                        <i class="fi fi-rr-settings" style="font-size: 48px; color: var(--primary);"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1">{{ $setting->key }}</h3>
                                        @if ($setting->type)
                                            <span class="badge bg-info">{{ ucfirst($setting->type) }}</span>
                                        @endif
                                        @if ($setting->category)
                                            <span class="badge bg-secondary">{{ ucfirst($setting->category) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Setting Key</label>
                                <p class="mb-0"><code>{{ $setting->key }}</code></p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Setting Type</label>
                                <p class="mb-0">{{ ucfirst($setting->type ?? 'string') }}</p>
                            </div>

                            @if ($setting->category)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted mb-1">Category</label>
                                    <p class="mb-0">{{ ucfirst($setting->category) }}</p>
                                </div>
                            @endif

                            <div class="col-12 mb-3">
                                <label class="form-label text-muted mb-1">Setting Value</label>
                                <div class="p-3 bg-light rounded">
                                    @if ($setting->type === 'boolean')
                                        <span class="badge {{ $setting->value ? 'bg-success' : 'bg-danger' }}">
                                            {{ $setting->value ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    @elseif($setting->type === 'json')
                                        <pre class="mb-0"><code>{{ json_encode(json_decode($setting->value), JSON_PRETTY_PRINT) }}</code></pre>
                                    @elseif($setting->type === 'file' || $setting->type === 'image')
                                        @if ($setting->value)
                                            <img src="{{ asset('storage/' . $setting->value) }}" alt="{{ $setting->key }}"
                                                class="img-thumbnail" style="max-width: 300px;">
                                        @else
                                            <p class="text-muted mb-0">No file uploaded</p>
                                        @endif
                                    @else
                                        <p class="mb-0">{{ $setting->value ?? 'Not set' }}</p>
                                    @endif
                                </div>
                            </div>

                            @if ($setting->description)
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted mb-1">Description</label>
                                    <p class="mb-0">{{ $setting->description }}</p>
                                </div>
                            @endif

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Created At</label>
                                <p class="mb-0">{{ format_date($setting->created_at) }}</p>
                                <small class="text-muted">{{ $setting->created_at->diffForHumans() }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Last Updated</label>
                                <p class="mb-0">{{ format_date($setting->updated_at) }}</p>
                                <small class="text-muted">{{ $setting->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Setting Meta -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Setting Metadata</h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <div class="mb-2">
                                <strong>Setting ID:</strong>
                                <span class="text-muted">#{{ $setting->id }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Data Type:</strong>
                                <span class="badge bg-info">{{ ucfirst($setting->type ?? 'string') }}</span>
                            </div>
                            @if ($setting->category)
                                <div class="mb-2">
                                    <strong>Category:</strong>
                                    <span class="badge bg-secondary">{{ ucfirst($setting->category) }}</span>
                                </div>
                            @endif
                            <div class="mb-2">
                                <strong>Autoload:</strong>
                                @if ($setting->autoload ?? false)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </div>
                            <div class="mb-2">
                                <strong>Created:</strong>
                                <br><span class="text-muted">{{ $setting->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Last Modified:</strong>
                                <br><span class="text-muted">{{ $setting->updated_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <!-- Usage Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">How to Use This Setting</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>In Blade Templates:</strong></p>
                        <pre class="bg-light p-3 rounded"><code>{{ '{' . '{ setting(\'' . $setting->key . '\') }' . '}' }}</code></pre>

                        <p class="mb-2 mt-3"><strong>In Controllers:</strong></p>
                        <pre class="bg-light p-3 rounded"><code>$value = setting('{{ $setting->key }}');</code></pre>

                        <p class="mb-2 mt-3"><strong>With Default Value:</strong></p>
                        <pre class="bg-light p-3 rounded"><code>$value = setting('{{ $setting->key }}', 'default_value');</code></pre>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Setting Type Info -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Data Type Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            @switch($setting->type ?? 'string')
                                @case('string')
                                    <p class="mb-0"><i class="fi fi-rr-text text-primary me-1"></i> <strong>String:</strong>
                                        Plain text value</p>
                                @break

                                @case('integer')
                                    <p class="mb-0"><i class="fi fi-rr-calculator text-success me-1"></i>
                                        <strong>Number:</strong> Numeric value</p>
                                @break

                                @case('float')
                                    <p class="mb-0"><i class="fi fi-rr-calculator text-success me-1"></i>
                                        <strong>Float:</strong> Decimal value</p>
                                @break

                                @case('boolean')
                                    <p class="mb-0"><i class="fi fi-rr-toggle-on text-warning me-1"></i>
                                        <strong>Boolean:</strong> True/False value</p>
                                @break

                                @case('json')
                                    <p class="mb-0"><i class="fi fi-rr-brackets-curly text-info me-1"></i>
                                        <strong>JSON:</strong> Structured data</p>
                                @break

                                @case('file')
                                    <p class="mb-0"><i class="fi fi-rr-file text-secondary me-1"></i> <strong>File:</strong>
                                        File upload</p>
                                @break

                                @case('image')
                                    <p class="mb-0"><i class="fi fi-rr-picture text-danger me-1"></i> <strong>Image:</strong>
                                        Image file</p>
                                @break

                                @default
                                    <p class="mb-0"><i class="fi fi-rr-document text-muted me-1"></i> <strong>Default:</strong>
                                        Mixed type</p>
                            @endswitch
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Related Settings -->
                @if ($setting->category)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Related Settings</h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-2">Other settings in
                                <strong>{{ ucfirst($setting->category) }}</strong> category</p>
                            @php
                                $relatedSettings = \App\Models\Setting::where('category', $setting->category)
                                    ->where('id', '!=', $setting->id)
                                    ->limit(5)
                                    ->get();
                            @endphp
                            @if ($relatedSettings->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach ($relatedSettings as $related)
                                        <li class="list-group-item px-0 py-2 border-0">
                                            <a href="{{ route('settings.show', $related) }}"
                                                class="text-decoration-none">
                                                <small><i class="fi fi-rr-settings me-1"></i>{{ $related->key }}</small>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small mb-0">No related settings found</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Add tooltip for copy button feedback
        document.addEventListener('DOMContentLoaded', function() {
            const copyBtn = document.querySelector('button[onclick*="clipboard"]');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fi fi-rr-check me-1"></i> Copied!';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                });
            }
        });
    </script>
@endpush
