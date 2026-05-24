<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Pathology Orders</h6>
        </div>
        <div>
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a data-size="lg" class="btn btn-primary px-2 w-100 w-sm-auto"
                    data-url="{{ route('ipd-patients.pathology-orders', $iPDPatient->id) }}" data-ajax-popup="true"
                    data-title="Add Pathology Order" data-bs-toggle="tooltip" title="Add Pathology Order"
                    data-original-title="Add Pathology Order"><i class="bi bi-plus-lg me-1"></i>
                    Add Pathology Order</a>
            @endif
        </div>
    </div>

    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th width="3%">SN</th>
                <th>Order #</th>
                <th width="12%">Date/Time</th>
                <th>Investigations</th>
                <th>Priority</th>
                <th>Doctor</th>
                <th>Lab</th>
                <th>Collected By</th>
                <th width="5%">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($iPDPatient->pathologyOrders->sortByDesc('datetime') as $order)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $order->order_number ?? '-' }}</td>
                    <td>{{ $order->datetime ? $order->datetime->format('d M Y h:i A') : '-' }}</td>
                    <td>
                        @forelse ($order->requests as $req)
                            <div>
                                <span
                                    class="badge bg-light text-dark border">{{ $req->labInvestigation->name ?? '-' }}</span>
                                @if ($req->labInvestigationCategory)
                                    <small class="text-muted">({{ $req->labInvestigationCategory->name }})</small>
                                @endif
                            </div>
                        @empty
                            -
                        @endforelse
                    </td>
                    <td>
                        @if ($order->priority)
                            @php
                                $priorityClass = match (strtolower($order->priority)) {
                                    'urgent' => 'bg-danger',
                                    'regular' => 'bg-success',
                                    'stat' => 'bg-info',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $priorityClass }}">{{ ucfirst($order->priority) }}</span>
                        @endif
                    </td>
                    <td>{{ $order->doctor->name ?? '-' }}</td>
                    <td>{{ $order->lab_name ?? '-' }}</td>
                    <td>{{ $order->collected_by ?? '-' }}</td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('ipd-patients.pathology-orders.show', [$iPDPatient->id, $order->id]) }}">
                                        <i class="bi bi-eye text-info me-2"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" data-size="lg"
                                        data-url="{{ route('ipd-patients.pathology-orders.edit', [$iPDPatient->id, $order->id]) }}"
                                        data-ajax-popup="true" data-title="Edit Pathology Order" href="#">
                                        <i class="bi bi-pencil text-primary me-2"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <form
                                        action="{{ route('ipd-patients.pathology-orders.destroy', [$iPDPatient->id, $order->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this pathology order?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-trash text-danger me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No pathology orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
