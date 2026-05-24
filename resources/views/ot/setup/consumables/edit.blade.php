@extends('backend.layouts.master')
@section('title','Edit Consumable')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Edit — {{ $item->name }}</h1>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form action="{{ route('ot.setup.consumables.update', $item->id) }}" method="POST">@csrf @method('PUT')
        <div class="card"><div class="card-body">@include('ot.setup.consumables._form')</div>
        <div class="card-footer text-end"><button class="btn btn-primary">Save</button></div></div>
    </form>
</div>
@endsection
