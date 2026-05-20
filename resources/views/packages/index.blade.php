@extends('backend.layouts.master')

@section('title', 'packages')

@section('content')
    <div class="container">
        {{-- Page Head --}}
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">packages</h1>
            </div>

            <a href="{{ route('packages.create') }}" class="btn btn-primary waves-effect waves-light">
                <i class="fi fi-rr-plus me-1"></i> Add package
            </a>
        </div>

        {{-- Table --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div
                        class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-2">package List</h6>
                        <div id="dt_packages_Search"></div>
                    </div>

                    <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                        <table id="dt_packages" class="table display table-row-rounded">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Package</th>
                                    <th>Discount %</th>
                                    <th>Total Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($packages as $p)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $p->name }}</td>
                                        <td>{{ $p->discount }}</td>
                                        <td>{{ number_format($p->total_amount, 2) }}</td>
                                        <td>
                                            <a href="{{ route('packages.edit', $p->id) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('packages.destroy', $p->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>


                                @endforeach
                            </tbody>
                        </table>

                        {{-- If you paginate --}}
                        <div class="mt-3 pb-3">
                            {{ $packages->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
