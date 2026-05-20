@extends('backend.layouts.master')

@section('title', 'Ambulance List')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Ambulances</h1>
        <a href="{{ route('amb.ambulances.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Add Ambulance
        </a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <table class="table display table-row-rounded">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Registration Number</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ambulances as $ambulance)
                        <tr>
                            <td>{{ $ambulance->id }}</td>
                            <td>{{ $ambulance->reg_no }}</td>
                            <td>{{ $ambulance->type }}</td>
                            <td>{{ $ambulance->status }}</td>
                            <td>
                                <a href="{{ route('amb.ambulances.show', $ambulance) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('amb.ambulances.edit', $ambulance) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('amb.ambulances.destroy', $ambulance) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this ambulance?')">Delete</button>
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
