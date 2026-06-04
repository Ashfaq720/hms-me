@push('styles')
    <style>
        .text-wrapper {
            display: inline-block;
            vertical-align: top;
        }

        .short-text {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            vertical-align: top;
        }

        .full-text {
            display: none;
            white-space: normal;
            word-wrap: break-word;
            line-height: 1.5;
            margin-top: 5px;
        }

        .read-more {
            font-size: 0.875rem;
            color: #007bff;
            cursor: pointer;
            margin-left: 5px;
            display: inline-block;
        }

        .read-more:hover {
            text-decoration: underline;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.text-wrapper .read-more', function(e) {
                e.preventDefault();

                const $this = $(this);
                const $wrapper = $this.closest('.text-wrapper');
                const $shortText = $wrapper.find('.short-text');
                const $fullText = $wrapper.find('.full-text');

                const readMoreText = $this.data('read-more');
                const readLessText = $this.data('read-less');

                if ($fullText.is(':visible')) {
                    // Collapse
                    $fullText.hide().addClass('d-none');
                    $shortText.show();
                    $this.text(readMoreText);
                } else {
                    // Expand
                    $shortText.hide();
                    $fullText.show().removeClass('d-none');
                    $this.text(readLessText);
                }
            });
        });
    </script>
@endpush