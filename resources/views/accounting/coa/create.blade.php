@extends('backend.layouts.master')
@section('title', $account->exists ? 'Edit Account' : 'Add Account')
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $account->exists ? 'Edit Account' : 'Add Chart of Account' }}</h1>
    <form method="POST" action="{{ $account->exists ? route('accounting.coa.update',$account) : route('accounting.coa.store') }}" class="card p-4 mt-3">
        @csrf @if($account->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Code *</label><input name="code" class="form-control" value="{{ old('code',$account->code) }}" required></div>
            <div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" value="{{ old('name',$account->name) }}" required></div>
            <div class="col-md-3"><label class="form-label">Type *</label>
                <select name="account_type" class="form-select" required>
                    @foreach (['asset','liability','equity','income','expense'] as $t)
                        <option value="{{ $t }}" @selected(old('account_type',$account->account_type) === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Category</label><input name="category" class="form-control" value="{{ old('category',$account->category) }}"></div>
            <div class="col-md-4"><label class="form-label">Parent</label>
                <select name="parent_id" class="form-select"><option value="">None</option>
                    @foreach ($parents as $p) <option value="{{ $p->id }}" @selected(old('parent_id',$account->parent_id) == $p->id)>{{ $p->code }} - {{ $p->name }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input type="hidden" name="is_postable" value="0"><input type="checkbox" name="is_postable" value="1" id="ip" class="form-check-input" @checked(old('is_postable',$account->is_postable ?? true))><label class="form-check-label" for="ip">Postable</label></div></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" id="ia" class="form-check-input" @checked(old('is_active',$account->is_active ?? true))><label class="form-check-label" for="ia">Active</label></div></div>
        </div>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('accounting.coa.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
