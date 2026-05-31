@once
    @push('scripts')
        <script>
            function optionPicker(options, initialId) {
                var list = Array.isArray(options) ? options : [];
                var normalizedInitialId = initialId == null ? '' : String(initialId);
                var hasInitial = list.some(function (item) {
                    return item.id === normalizedInitialId;
                });

                return {
                    open: false,
                    options: list,
                    selectedId: hasInitial ? normalizedInitialId : '',
                    selected: function () {
                        var current = this.selectedId;
                        return this.options.find(function (item) {
                            return item.id === current;
                        }) || null;
                    },
                    pick: function (id) {
                        this.selectedId = id;
                        this.open = false;
                    },
                };
            }

            // Backwards-compatible aliases used by the application form.
            function countryPicker(options, initialId) { return optionPicker(options, initialId); }
            function platformPicker(options, initialId) { return optionPicker(options, initialId); }
        </script>
    @endpush
@endonce
