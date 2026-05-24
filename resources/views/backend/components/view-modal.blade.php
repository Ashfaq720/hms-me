<!-- Global Dynamic View Modal -->
<div id="global-view-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="globalViewModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="globalViewModalLabel">View Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="global-view-modal-body">
                <!-- AJAX content will load here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.open-view-modal', function(e) {
                e.preventDefault();

                const url = $(this).data('url');
                const modalTitle = $(this).data('title') || 'View Item';

                // Set modal title
                $('#global-view-modal .modal-title').text(modalTitle);

                // Show loading while fetching content
                $('#global-view-modal-body').html('<div class="text-center p-3"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#global-view-modal-body').html(response);
                        $('#global-view-modal').modal('show');
                        // init select2 if present inside view modal
                        if (typeof initSelect2 === 'function') { initSelect2(); }
                    },
                    error: function() {
                        $('#global-view-modal-body').html(
                            '<div class="alert alert-danger">Failed to load content.</div>');
                    }
                });
            });
        });
    </script>
@endpush