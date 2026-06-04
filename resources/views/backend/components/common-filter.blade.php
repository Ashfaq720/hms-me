<form id="common-filter-form" action="{{ $route }}" method="{{ $method ?? 'GET' }}" class="mb-0">
    {{-- CSRF token for security --}}
    @csrf
    
    <div class="row filter-row">
        @includeIf($filterView)
    </div>
</form>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#common-filter-form').on('submit', function(e) {
                // You can add AJAX logic here if needed
            });

            // Reset form fields and trigger submit
            $('.reset-filter').on('click', function () {
                const form = $('#common-filter-form');

                // Clear all inputs
                form.find('input[type="text"], input[type="date"], input[type="search"], input[type="number"]').val('');
                form.find('select').prop('selectedIndex', 0);

                // Clear datetimepicker fields explicitly
                form.find('.datetimepicker').each(function () {
                    $(this).data("DateTimePicker")?.clear();
                });

                // Submit the form
                form.submit();
            });
        });
    </script>
@endpush
