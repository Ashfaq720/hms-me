<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Patient Charges</h6>
        </div>

        <div>
            <a href="{{ route('ipd-patients.charges.create', $iPDPatient->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Add Charge
            </a>
        </div>
    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>SN</th>
                <th scope="col">Date</th>
                <th scope="col">Charge Item</th>
                <th scope="col">Unit Price</th>
                <th scope="col">Qty</th>
                <th scope="col">Amount</th>
                <th scope="col">Vat</th>
                <th scope="col">Tax</th>
                <th scope="col">Net Amount</th>
                {{-- <th scope="col">Paid</th> --}}
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($charges as $charge)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ format_datetime($charge->date) ?? 'N/A' }}</td>
                    <td>
                        @if ($charge->charge_id)
                            {{ $charge->charge->charge_name ?? 'N/A' }}
                        @else
                            {{ $charge->charge_item ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($charge->unit_price, 2) }}</td>
                    <td class="text-center">{{ $charge->quantity }}</td>
                    <td class="text-end">{{ number_format($charge->amount, 2) }}</td>
                    <td class="text-end">{{ number_format($charge->vat, 2) }}</td>
                    <td class="text-end">{{ number_format($charge->tax, 2) }}</td>
                    <td class="text-end fw-semibold">{{ number_format($charge->net_amount, 2) }}</td>

                    {{-- <td class="text-center">
                        @if ($charge->is_paid)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td> --}}

                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            @if ($charge->charge_id)
                                <a href="{{ route('ipd-patients.charges.edit', [$iPDPatient->id, $charge->id]) }}"
                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                            <form action="{{ route('ipd-patients.charges.destroy', [$iPDPatient->id, $charge->id]) }}"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this charge?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No charges available.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($charges->count())
            <tfoot class="table-light">
                <tr>
                    <td colspan="8" class="text-end fw-bold">Total:</td>
                    <td class="text-end fw-bold">{{ number_format($charges->sum('net_amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
