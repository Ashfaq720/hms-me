<!-- Common Delete Modal -->
<div class="modal fade" id="delete_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="deleteModalLabel" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-body">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    </div>
                    <h5 id="deleteModalLabel">Delete Confirmation</h5>
                    <p class="text-muted">Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
                <div class="d-flex justify-content-center gap-3">
                    <form action="" method="POST" id="common-delete-form">
                        @csrf
                        @method('DELETE')

                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Yes, Delete
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Common Delete Modal Handler
            $(document).on('click', '.delete_modal', function () {
                let deleteUrl = $(this).data('url'); // get dynamic delete URL
                let id = $(this).data('id');         // optional: set hidden ID field
                let title = $(this).data('title') || 'Delete Item'; // optional: set modal title

                $('#common-delete-form').attr('action', deleteUrl); // set form action
                $('#delete_id').val(id); // optional: set hidden input field
                $('#deleteModalLabel').text(title); // set modal title
            });
        });
    </script>
@endpush
