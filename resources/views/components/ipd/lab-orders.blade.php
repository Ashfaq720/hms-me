<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Lab Orders ({{ $orders->count() }})</h6>
            <small class="text-muted">All investigations — grouped by type. Pricing pulled from Lab Investigations master.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ipd-patients.lab-orders.create', $iPDPatient->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> New Lab Order
                <small class="ms-1 opacity-75">(all 9 types · multi-line)</small>
            </a>
        </div>
    </div>

    @php
        $typeMeta = [
            'pathology' => ['Pathology / Lab', 'info', 'eyedropper'],
            'radiology' => ['Radiology / Imaging', 'primary', 'broadcast'],
        ];
    @endphp

    @forelse ($byType as $type => $orders)
        @php [$label, $colour, $icon] = $typeMeta[$type] ?? [ucfirst($type), 'secondary', 'list']; @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-{{ $colour }} bg-opacity-10 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-{{ $colour }}">
                    <i class="bi bi-{{ $icon }}"></i> {{ $label }}
                    <span class="badge bg-{{ $colour }} ms-1">{{ $orders->count() }}</span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>Order No</th><th>Date</th><th>Investigation</th>
                            <th>Doctor</th><th>Priority</th><th>Status</th><th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $o)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $o->order_number ?? $o->id }}</strong></td>
                                <td><small>{{ \Carbon\Carbon::parse($o->datetime ?? $o->created_at)->format('Y-m-d H:i') }}</small></td>
                                <td>{{ $o->remarks }}</td>
                                <td>{{ $o->doctor_name ?? '—' }}</td>
                                <td>
                                    @php $pCol = ['urgent' => 'danger', 'stat' => 'danger', 'routine' => 'secondary'][$o->priority ?? 'routine'] ?? 'secondary'; @endphp
                                    <span class="badge bg-{{ $pCol }}">{{ strtoupper($o->priority ?? 'routine') }}</span>
                                </td>
                                <td>
                                    @php $sCol = ['completed' => 'success', 'in_progress' => 'warning text-dark', 'pending' => 'secondary'][$o->status ?? 'pending'] ?? 'secondary'; @endphp
                                    <span class="badge bg-{{ $sCol }}">{{ ucfirst($o->status ?? 'pending') }}</span>
                                </td>
                                <td><small>{{ $o->source ?? '—' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-clipboard display-4"></i>
            <p class="mt-3 mb-1">No lab orders yet</p>
            <small>Use the buttons above to add a Pathology or Radiology order — price will auto-fetch from the Lab Investigations master and post to the encounter charge.</small>
        </div>
    @endforelse
</div>
