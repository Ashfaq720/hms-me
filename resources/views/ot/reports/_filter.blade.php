<form method="GET" class="card card-body mb-3">
    <div class="row g-2">
        <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="{{ request('from', $from?->toDateString()) }}"></div>
        <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="{{ request('to', $to?->toDateString()) }}"></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary">Filter</button></div>
    </div>
</form>
