@extends('backend.layouts.master')
@section('title','New Equipment')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">New Equipment</h1>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form action="{{ route('ot.setup.equipments.store') }}" method="POST">@csrf
        <div class="card"><div class="card-body">@include('ot.setup.equipments._form')</div>
        <div class="card-footer text-end"><button class="btn btn-primary">Save</button></div></div>
    </form>
</div>
@endsection
