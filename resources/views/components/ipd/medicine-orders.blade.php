<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Medicine Orders</h6>
        </div>
        @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
            <a href="javascript:void(0);" class="btn btn-sm btn-primary"
                data-url="{{ route('ipd-patients.medicine-orders.create', $iPDPatient->id) }}" data-ajax-popup="true"
                data-title="Add Medicine Order" data-size="xl">
                <i class="bi bi-plus"></i> Add Medicine Order
            </a>
        @endif
    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>SN</th>
                <th>Medicine</th>
                <th>Unit</th>
                <th>Category</th>
                <th>Group</th>
                <th>Qty</th>
                <th>Prescribed By</th>
                <th>Status</th>
                <th>Order By</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($iPDPatient->medicineOrders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $order->medicine->medicine_name ?? 'N/A' }}</td>
                    <td>{{ $order->medicine->unit?->name ?? '-' }}</td>
                    <td>{{ $order->medicine->category?->name ?? '-' }}</td>
                    <td>{{ $order->medicine->medicalGroup?->name ?? '-' }}</td>
                    <td>{{ $order->qty }}</td>
                    <td>{{ $order->prescribedBy->name ?? 'N/A' }}</td>
                    <td>
                        @if ($order->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif($order->status === 'dispensed')
                            <span class="badge bg-info">Dispensed</span>
                        @elseif($order->status === 'cancelled')
                            <span class="badge bg-danger">Cancelled</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>{{ $order->order_by ?? '-' }}</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary"
                            data-url="{{ route('ipd-patients.medicine-orders.edit', [$iPDPatient->id, $order->id]) }}"
                            data-ajax-popup="true" data-title="Edit Medicine Order">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('ipd-patients.medicine-orders.destroy', [$iPDPatient->id, $order->id]) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirm('Are you sure you want to delete this order?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">No medicine orders available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
