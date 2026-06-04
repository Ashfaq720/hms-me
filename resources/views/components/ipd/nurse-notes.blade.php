<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Nurse Observations</h6>
        </div>
        <div>
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a data-size="lg" class="btn btn-primary px-2 w-100 w-sm-auto"
                    data-url="{{ route('ipd-patients.nurse-notes', $iPDPatient->id) }}" data-ajax-popup="true"
                    data-title="Add Note" data-bs-toggle="tooltip" title="Add Observation"
                    data-original-title="Add Observation"><i class="bi bi-plus-lg me-1"></i>
                    Add Observations</a>
            @endif
        </div>

    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th width="3%">SN</th>
                <th width="10%">Title</th>
                <th width="10%">DateTime</th>
                <th width="8%">Shift</th>
                <th width="10%">Doctor</th>
                <th width="7%">Priority</th>
                <th width="20%">Note</th>
                <th width="15%">Observation</th>
                <th width="7%">By</th>
                <th width="5%">File</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($iPDPatient->nurseNotes->sortByDesc('date') as $note)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $note->title ?? '-' }}</td>
                    <td>{{ format_datetime($note->date) ?? 'N/A' }}</td>
                    <td>{{ $note->shift ?? '-' }}</td>
                    <td>{{ $note->doctor->name ?? '-' }}</td>
                    <td>
                        @if ($note->priority === 'Critical')
                            <span class="badge bg-danger">{{ $note->priority }}</span>
                        @elseif($note->priority === 'Urgent')
                            <span class="badge bg-warning">{{ $note->priority }}</span>
                        @else
                            <span class="badge bg-info">{{ $note->priority }}</span>
                        @endif
                    </td>
                    <td>{{ $note->note }}</td>
                    <td>{{ $note->observations ?? '-' }}</td>
                    <td>{{ $note->nurse_name }}</td>
                    <td>
                        @if ($note->file)
                            <a href="{{ asset('storage/' . $note->file) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-paperclip"></i>
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">No nurse notes found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Timeline View (Urgent only) --}}
    <div class="mt-4">
        <Nurse class="mb-3 fw-bold">Nurse Notes</h6>
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
