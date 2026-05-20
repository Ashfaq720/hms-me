<div class="modal fade" id="commonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commonModalTitle">Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="commonModalBody" style="overflow:visible;">
                <div class="text-center py-4">Loading...</div>
            </div>
        </div>
    </div>
</div>

<style>
/* Ensure Select2 dropdowns inside any modal render above the modal overlay */
.modal .select2-container--open .select2-dropdown {
    z-index: 1060;
}
/* Prevent modal-body overflow from clipping the dropdown */
.modal-body {
    overflow: visible !important;
}
/* But keep the scrollable container scrolling naturally */
.modal-dialog-scrollable .modal-body {
    overflow-y: auto !important;
    overflow-x: visible !important;
}
</style>
