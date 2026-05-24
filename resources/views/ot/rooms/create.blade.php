@extends('backend.layouts.master')
@section('title','New OT Room')
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between"><h1 class="app-page-title">New OT Room</h1>
        <a href="{{ route('ot.rooms.index') }}" class="btn btn-outline-secondary">Back</a></div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form action="{{ route('ot.rooms.store') }}" method="POST">@csrf
        <div class="card"><div class="card-body">@include('ot.rooms._form')</div>
        <div class="card-footer text-end"><button class="btn btn-primary">Create Room</button></div></div>
    </form>
</div>
@endsection
