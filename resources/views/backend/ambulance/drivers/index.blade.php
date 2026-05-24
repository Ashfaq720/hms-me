@extends('backend.layouts.master')

@section('title', 'Driver List')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Drivers</h1>
        <a href="{{ route('amb.drivers.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Add Driver
        </a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <table class="table display table-row-rounded">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>License Number</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($drivers as $driver)
                        <tr>
                            <td>{{ $driver->id }}</td>
                            <td>{{ $driver->name }}</td>
                            <td>{{ $driver->license_number }}</td>
                            <td>{{ $driver->status }}</td>
                            <td>
                                <a href="{{ route('amb.drivers.show', $driver) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('amb.drivers.edit', $driver) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('amb.drivers.destroy', $driver) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this driver?')">Delete</button>
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
