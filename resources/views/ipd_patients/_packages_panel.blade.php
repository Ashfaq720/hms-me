@php
    /**
     * Package picker partial for the IPD admission form.
     *
     * Inputs:
     *   $attachedPackages   — collection of IpdPatientPackage already attached
     *                          (empty collection on create; populated on edit)
     *
     * Server-side requirements when this partial submits:
     *   packages[]                — array of {package_id, agreed_price, price_override?, remarks?}
     *   removed_package_ids[]     — array of attached-package ids to detach
     *
     * The picker filters by patient_type (currently fixed to IPD on this
     * form) + department + bed type — once those are chosen elsewhere on
     * the page, JS narrows the list.
     */
    $activePackages = \App\Models\ServicePackage::active()
        ->with(['bedType', 'department', 'bedPrices'])
        ->orderBy('name')
        ->get();
    $bedTypeMap = \App\Models\BedType::pluck('name', 'id');

    // Precompute the JS payload here so Blade doesn't have to parse
    // nested `[...]` literals inside @json(...) below.
    $ipdPkgJsOptions = $activePackages->map(function ($p) use ($bedTypeMap) {
        return [
            'id'             => $p->id,
            'code'           => $p->code,
            'name'           => $p->name,
            'package_type'   => $p->package_type,
            'department_id'  => $p->department_id,
            'bed_type_id'    => $p->bed_type_id,
            'bed_type_name'  => $p->bed_type_id ? ($bedTypeMap[$p->bed_type_id] ?? null) : null,
            'base_price'     => (float) $p->base_price,
            'requires_appr'  => (bool) $p->requires_approval,
            // bed_type_id => price. JS uses this to recompute agreed price
            // once the auto-allocated bed is known.
            'bed_prices'     => $p->bedPrices->pluck('price', 'bed_type_id')
                ->map(fn ($v) => (float) $v)
                ->toArray(),
        ];
    })->values();
@endphp

<div class="alert alert-info py-2 small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    Attach one or more <strong>Service Packages</strong> to this admission. Package price is
    snapshotted at apply-time. Status starts as <em>Pending Approval</em> for packages whose
    master requires approval, otherwise <em>Confirmed</em>. Already-attached packages can be
    removed only if they are not yet billed.
</div>

@if($attachedPackages->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header py-2"><strong>Already attached ({{ $attachedPackages->count() }})</strong></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th><th>Name</th><th>Type</th><th>Status</th>
                        <th class="text-end">Agreed Price</th>
                        <th>Applied At</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attachedPackages as $att)
                        <tr>
                            <td><strong>{{ optional($att->package)->code }}</strong></td>
                            <td>{{ optional($att->package)->name }}</td>
                            <td><span class="badge bg-info">{{ optional($att->package)->package_type }}</span></td>
                            <td><span class="badge {{ $att->status_badge_class }}">{{ $att->status }}</span></td>
                            <td class="text-end">৳{{ number_format($att->effectivePrice(), 2) }}</td>
                            <td class="small text-muted">{{ optional($att->applied_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                @if($att->canBeCancelled())
                                    <label class="small text-danger">
                                        <input type="checkbox" name="removed_package_ids[]" value="{{ $att->id }}"
                                               class="form-check-input me-1">
                                        Remove
                                    </label>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <strong>Attach New Package</strong>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="ipdPkgAddRow()">
            <i class="bi bi-plus-lg"></i> Add Package
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:35%">Package <small class="text-muted d-block fw-normal">(filtered by Active status)</small></th>
                    <th>Default Bed Type</th>
                    <th class="text-end">Agreed Price</th>
                    <th>Price Override</th>
                    <th>Remarks</th>
                    <th style="width:5%"></th>
                </tr>
            </thead>
            <tbody id="ipd-pkg-rows">
                {{-- rows added by JS --}}
            </tbody>
        </table>
    </div>
    <div class="card-footer small text-muted">
        <i class="bi bi-lightbulb"></i>
        For OT-category packages, the OT booking screen will offer a <strong>"Use IPD Package"</strong>
        button that pre-fills the surgery type and posts the package's bundled price.
    </div>
</div>

@push('scripts')
<script>
    const IPD_PKG_OPTIONS = @json($ipdPkgJsOptions);

    function ipdPkgRenderOption(opt) {
        return `<option value="${opt.id}"
                        data-price="${opt.base_price}"
                        data-bed="${opt.bed_type_name || ''}"
                        data-appr="${opt.requires_appr ? '1' : '0'}">
                    ${opt.code} · ${opt.name} (${opt.package_type})
                </option>`;
    }

    function ipdPkgAddRow() {
        const tbody = document.getElementById('ipd-pkg-rows');
        const i     = tbody.children.length;
        const opts  = IPD_PKG_OPTIONS.map(ipdPkgRenderOption).join('');
        const tr    = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="packages[${i}][package_id]" class="form-select form-select-sm ipd-pkg-sel" required>
                    <option value="">— Select package —</option>
                    ${opts}
                </select>
            </td>
            <td><span class="ipd-pkg-bed text-muted small">—</span></td>
            <td class="text-end">
                <input type="number" min="0" step="0.01" name="packages[${i}][agreed_price]"
                       class="form-control form-control-sm text-end ipd-pkg-price" placeholder="0.00" required>
                <small class="ipd-pkg-price-hint text-muted d-block" style="font-size: 10px;"></small>
            </td>
            <td>
                <input type="number" min="0" step="0.01" name="packages[${i}][price_override]"
                       class="form-control form-control-sm" placeholder="Optional override">
                <span class="ipd-pkg-appr-flag small text-warning d-none">
                    <i class="bi bi-shield-exclamation"></i> Approval required
                </span>
            </td>
            <td>
                <input type="text" name="packages[${i}][remarks]" class="form-control form-control-sm" placeholder="—">
            </td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>`;
        tbody.appendChild(tr);

        const sel = tr.querySelector('.ipd-pkg-sel');
        sel.addEventListener('change', function () {
            const opt   = this.options[this.selectedIndex];
            const pkgId = parseInt(this.value || '0', 10);
            const pkg   = IPD_PKG_OPTIONS.find(p => p.id === pkgId);

            // Default agreed price = bed-type-specific (if package has
            // variant pricing for its default bed) else base_price.
            let agreed   = parseFloat(opt.dataset.price || '0');
            let priceSrc = 'base price';
            if (pkg && pkg.bed_type_id && pkg.bed_prices && pkg.bed_prices[pkg.bed_type_id]) {
                agreed   = parseFloat(pkg.bed_prices[pkg.bed_type_id]);
                priceSrc = (pkg.bed_type_name || 'bed') + ' variant price';
            }
            tr.querySelector('.ipd-pkg-price').value = agreed.toFixed(2);
            const hint = tr.querySelector('.ipd-pkg-price-hint');
            if (hint) hint.textContent = 'auto: ' + priceSrc;
            tr.querySelector('.ipd-pkg-bed').textContent = opt.dataset.bed || '—';
            tr.querySelector('.ipd-pkg-appr-flag')
                .classList.toggle('d-none', opt.dataset.appr !== '1');

            // Fire the bed-auto-select event with full pricing context
            // so the form listener can recompute when the bed is set.
            if (pkg && pkg.bed_type_id) {
                document.dispatchEvent(new CustomEvent('package:selected', {
                    detail: {
                        package_id:  pkg.id,
                        bed_type_id: pkg.bed_type_id,
                        base_price:  pkg.base_price,
                        bed_prices:  pkg.bed_prices || {},
                        price_input: tr.querySelector('.ipd-pkg-price'),
                    },
                }));
            }
        });
    }

    /**
     * Listener: when a package is picked, auto-select the first bed in
     * the IPD form whose data-bed-type-id matches the package's default
     * bed type. Shows a visible toast/banner confirming what happened.
     * Skips silently if the user has already picked a bed.
     */
    document.addEventListener('package:selected', function (ev) {
        const bedTypeId = String(ev.detail?.bed_type_id || '');
        if (! bedTypeId) return;

        let pickedBedLabel = null;
        let pickedBedTypeId = null;

        ['select[name="bed_id"]', 'select[name="icu_bed_id"]'].forEach(function (selector) {
            const sel = document.querySelector(selector);
            if (! sel || sel.value) {
                // Already picked — read its bed_type_id so we can still
                // recompute price for it below.
                if (sel && sel.value) {
                    const cur = sel.options[sel.selectedIndex];
                    if (cur && cur.dataset && cur.dataset.bedTypeId) {
                        pickedBedTypeId = cur.dataset.bedTypeId;
                    }
                }
                return;
            }

            const match = Array.from(sel.options).find(o =>
                o.dataset && o.dataset.bedTypeId === bedTypeId && o.value
            );
            if (! match) return;

            // Set value + trigger change for native listeners AND select2
            sel.value = match.value;
            sel.dispatchEvent(new Event('change', { bubbles: true }));
            if (window.jQuery) {
                window.jQuery(sel).val(match.value).trigger('change');
            }
            pickedBedLabel  = (match.textContent || '').trim().split(/\s{2,}|\(/)[0].trim();
            pickedBedTypeId = match.dataset.bedTypeId;
        });

        // Recompute the agreed price using the bed-wise variant of the
        // bed that's actually allocated (may differ from the package's
        // default bed when the user pre-picked a different bed).
        const bedPrices = ev.detail?.bed_prices || {};
        const priceInp  = ev.detail?.price_input;
        if (pickedBedTypeId && priceInp && bedPrices[pickedBedTypeId]) {
            priceInp.value = parseFloat(bedPrices[pickedBedTypeId]).toFixed(2);
        }

        // Visible confirmation so the user understands what just happened.
        if (pickedBedLabel) {
            if (window.toastr) {
                window.toastr.success('Auto-selected bed: <strong>' + pickedBedLabel + '</strong>', '', { escapeHtml: false, timeOut: 4000 });
            }
            // Also drop a small inline banner near the package picker that
            // sticks until the user changes the package — works even if
            // toastr isn't loaded on this page.
            const banner = document.getElementById('ipd-pkg-bed-banner') || (function () {
                const el = document.createElement('div');
                el.id = 'ipd-pkg-bed-banner';
                el.className = 'alert alert-success py-2 small mb-2';
                const host = document.querySelector('#packageCollapse .accordion-body, #ipd-pkg-rows');
                if (host) host.prepend(el);
                return el;
            })();
            banner.innerHTML =
                '<i class="bi bi-check-circle me-1"></i> ' +
                'Bed auto-allocated: <strong>' + pickedBedLabel + '</strong> ' +
                '(matches package\'s default bed type). ' +
                '<span class="text-muted">Change manually in Step 2 if needed.</span>';
        }
    });
</script>
@endpush
