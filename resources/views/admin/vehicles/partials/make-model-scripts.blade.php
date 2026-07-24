<script>
    $(function () {
        // Make => [models] master data from the Vehicle Maintenance Configuration module.
        var makeModelMap = @json($makeModelMap ?? []);

        var $make = $('#vehicle_make_select');
        var $model = $('#vehicle_model_select');

        if (!$make.length || !$model.length) {
            return;
        }

        // Rebuild the dependent Model dropdown for the chosen Make. A saved
        // value that isn't in the configuration (e.g. legacy data) is kept so
        // the vehicle can still be edited without losing its current model.
        function buildModels(make, selectedModel) {
            selectedModel = (selectedModel === null || selectedModel === undefined) ? '' : String(selectedModel);

            var models = (make && makeModelMap[make]) ? makeModelMap[make].slice() : [];
            if (selectedModel && models.indexOf(selectedModel) === -1) {
                models.push(selectedModel);
            }

            $model.empty().append(new Option('-- Select Model --', '', false, false));
            models.forEach(function (m) {
                $model.append(new Option(m, m, false, String(m) === selectedModel));
            });

            $model.trigger('change.select2');
        }

        $make.select2({ width: '100%', placeholder: '-- Select Make --', allowClear: true });
        $model.select2({ width: '100%', placeholder: '-- Select Model --', allowClear: true });

        // Initial state (edit page, or create page after a validation error).
        buildModels($make.val(), $model.data('selected'));

        // Changing the Make refreshes the Model list and clears the selection.
        $make.on('change', function () {
            buildModels($(this).val(), null);
        });
    });
</script>
