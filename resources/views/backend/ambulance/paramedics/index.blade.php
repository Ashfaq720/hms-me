@extends('backend.layouts.master')

@section('title', 'Paramedic List')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Paramedics</h1>
        <a href="{{ route('amb.paramedics.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Add Paramedic
        </a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <table class="table display table-row-rounded">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Certification</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paramedics as $paramedic)
                        <tr>
                            <td>{{ $paramedic->id }}</td>
                            <td>{{ $paramedic->name }}</td>
                            <td>{{ $paramedic->certification }}</td>
                            <td>{{ $paramedic->status }}</td>
                            <td>
                                <a href="{{ route('amb.paramedics.show', $paramedic) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('amb.paramedics.edit', $paramedic) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('amb.paramedics.destroy', $paramedic) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this paramedic?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
