<!-- Global Dynamic Create Modal -->
<div id="global-create-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="globalCreateModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="globalCreateModalLabel">Create New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="global-create-modal-body">
                <!-- AJAX content will load here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.open-create-modal', function(e) {
                e.preventDefault();

                const url = $(this).data('url');
                const modalTitle = $(this).data('title') || 'Create New Item';

                // Set modal title
                $('#global-create-modal .modal-title').text(modalTitle);

                // Show loading while fetching content
                $('#global-create-modal-body').html('<div class="text-center p-3"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#global-create-modal-body').html(response);
                        $('#global-create-modal').modal('show');

                        // Initialize form elements after content is loaded
                        initializeFormElements();
                    },
                    error: function() {
                        $('#global-create-modal-body').html(
                            '<div class="alert alert-danger">Failed to load content.</div>');
                    }
                });
            });

            function initializeFormElements() {
                // Initialize select2 if function available
                if (typeof initSelect2 === 'function') {
                    initSelect2();
                }
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
            }
        });
    </script>
@endpush
