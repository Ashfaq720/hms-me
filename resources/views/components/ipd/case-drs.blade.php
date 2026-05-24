<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Case Doctor Entries</h6>
        </div>
        <div>
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a data-size="lg" class="btn btn-primary px-2 w-100 w-sm-auto"
                    data-url="{{ route('ipd-patients.case-drs', $iPDPatient->id) }}" data-ajax-popup="true"
                    data-title="Add Case Dr Entry" data-bs-toggle="tooltip" title="Add Case Dr Entry">
                    <i class="bi bi-plus-lg me-1"></i> Add Case Dr
                </a>
            @endif
        </div>
    </div>

    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th width="3%">SN</th>
                <th width="12%">DateTime</th>
                <th width="10%">Doctor</th>
                <th width="8%">Shift</th>
                <th width="8%">Priority</th>
                <th width="10%">Order To</th>
                <th width="13%">Diagnosis</th>
                <th width="15%">Note</th>
                <th width="15%">Observations</th>
                <th width="16%">Order</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($iPDPatient->caseDrs->sortByDesc('datetime') as $entry)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ format_datetime($entry->datetime) ?? 'N/A' }}</td>
                    <td>{{ $entry->doctor->name ?? '-' }}</td>
                    <td>{{ $entry->shift ?? '-' }}</td>
                    <td>
                        @if ($entry->priority === 'Critical')
                            <span class="badge bg-danger">{{ $entry->priority }}</span>
                        @elseif($entry->priority === 'Urgent')
                            <span class="badge bg-warning">{{ $entry->priority }}</span>
                        @else
                            <span class="badge bg-info">{{ $entry->priority }}</span>
                        @endif
                    </td>
                    <td>{{ $entry->order_to ?? '-' }}</td>
                    <td>{{ $entry->diagnosis ?? '-' }}</td>
                    <td>{{ $entry->note ?? '-' }}</td>
                    <td>{{ $entry->observations ?? '-' }}</td>
                    <td>{{ $entry->order ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">No case doctor entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>


    {{-- Timeline View (Urgent only) --}}
    <div class="mt-4">
        <h6 class="mb-3 fw-bold">Nurse Notes</h6>
            @php
                $urgentNotes = $iPDPatient->nurseNotes->where('priority', 'Urgent')->sortByDesc('date');
            @endphp
            <div class="position-relative" style="padding-left: 40px;">
                {{-- Vertical line --}}
                <div class="position-absolute" style="left: 18px; top: 0; bottom: 0; width: 2px; background: #dee2e6;">
                </div>

                @forelse ($urgentNotes as $note)
                    <div class="position-relative mb-4">
                        {{-- Timeline icon --}}
                        <div class="position-absolute d-flex align-items-center justify-content-center rounded-circle bg-white border"
                            style="left: -32px; top: 0; width: 28px; height: 28px; z-index: 1;">
                            <i class="bi bi-shield-fill-check text-primary" style="font-size: 14px;"></i>
                        </div>

                        {{-- Date label --}}
                        <div class="text-muted small fw-semibold text-uppercase mb-1">
                            @if ($note->date->isToday())
                                TODAY, {{ format_datetime($note->date) }}
                            @elseif($note->date->isYesterday())
                                YESTERDAY, {{ format_datetime($note->date) }}
                            @else
                                {{ format_datetime($note->date) }}
                            @endif
                        </div>

                        {{-- Priority badge --}}
                        <span class="badge rounded-pill bg-warning text-dark mb-1">{{ $note->priority }}</span>

                        {{-- Title --}}
                        <div class="fw-bold">{{ $note->title ?? 'Untitled' }}</div>

                        {{-- Note content --}}
                        <div class="text-muted small">{{ $note->note }}</div>

                        {{-- Thread replies --}}
                        <div class="mt-2 ps-3 border-start">
                            @foreach ($note->replies as $reply)
                                <div class="mb-2">
                                    <div class="small">
                                        <span><strong>{{ $reply->user_name }}</strong>({{ $reply->user_role }})</span>
                                        {{-- @if ($reply->user_role)
                                        <span class="badge bg-secondary ms-1">{{ $reply->user_role }}</span>
                                    @endif --}}
                                        <span class="text-muted ms-1">{{ format_datetime($reply->created_at) }}</span>
                                    </div>
                                    <div class="text-muted small">{{ $reply->reply }}</div>
                                </div>
                            @endforeach

                            {{-- Reply form --}}
                            <form action="{{ route('ipd-patients.nurse-notes.reply', $note->id) }}" method="POST"
                                class="mt-2">
                                @csrf
                                <div class="input-group input-group-sm" style="max-width: 500px;">
                                    <input type="text" name="reply" class="form-control" placeholder="Reply..."
                                        required>
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-reply-fill"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No urgent notes.</div>
                @endforelse
            </div>
    </div>
</div>
