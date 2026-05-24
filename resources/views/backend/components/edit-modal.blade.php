<!-- Global Dynamic Edit Modal -->
<div id="global-edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="globalEditModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="globalEditModalLabel">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="global-edit-modal-body">
                <!-- AJAX content will load here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.open-edit-modal', function(e) {
                e.preventDefault();

                const url = $(this).data('url');
                const modalTitle = $(this).data('title') || 'Edit Item';

                // Set modal title
                $('#global-edit-modal .modal-title').text(modalTitle);

                // Show loading while fetching content
                $('#global-edit-modal-body').html('<div class="text-center p-3"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#global-edit-modal-body').html(response);
                        $('#global-edit-modal').modal('show');

                        // Initialize form elements after content is loaded
                        initializeFormElements();
                    },
                    error: function() {
                        $('#global-edit-modal-body').html(
                            '<div class="alert alert-danger">Failed to load content.</div>');
                    }
                });
            });

            function initializeFormElements() {
                // Initialize date/time pickers if available
                if (typeof $.fn.datetimepicker !== 'undefined') {
                    $('.datetimepicker').datetimepicker({
                        format: 'YYYY-MM-DD HH:mm'
                    });
                }

                if (typeof $.fn.datepicker !== 'undefined') {
                    $('.datepicker').datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true
                    });
                }
                // Initialize select2 if function available
                if (typeof initSelect2 === 'function') {
                    initSelect2();
                }
            }
        });
    </script>
@endpush
