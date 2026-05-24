@extends('backend.layouts.master')

@section('title', 'services')

@section('content')
    <div class="container">
        {{-- Page Head --}}
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">services</h1>
            </div>

            <a href="{{ route('services.create') }}" class="btn btn-primary waves-effect waves-light">
                <i class="fi fi-rr-plus me-1"></i> Add Service
            </a>
        </div>

        {{-- Table --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div
                        class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-2">Service List</h6>
                        <div id="dt_services_Search"></div>
                    </div>

                    <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                        <table id="dt_services" class="table display table-row-rounded">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Service</th>
                                    <th>Quantity</th>
                                    <th>Rate</th>
                                    <th>Status</th>
                                    <th width="220">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($services as $s)
                                    <tr>
                                        <td>{{ $s->id }}</td>
                                        <td>{{ $s->name ?? "" }}</td>
                                        <td>{{ $s->quantity ?? "" }}</td>
                                        <td>{{ $s->rate ?? "" }}</td>
                                        <td>
                                            @if ($s->status == 1)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex flex-wrap gap-1 justify-content-start">

                                                <a class="btn btn-sm btn-warning" title="Edit"
                                                    href="{{ route('services.edit', $s) }}"><i class="fa-solid fa-pen-to-square"></i></a>

                                                <form title="Delete" method="POST" action="{{ route('services.destroy', $s) }}"
                                                    onsubmit="return confirm('Delete this service?')" class="m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No data found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- If you paginate --}}
                        <div class="mt-3 pb-3">
                            {{ $services->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
