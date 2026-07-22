<script>
    $(function() {
        $('.select2').select2({ width: '100%' });

        // ── Work Done: searchable dropdown with add-new + delete ──────────
        function workDoneTemplate(item) {
            if (!item.id || !item.element) {
                return item.text;
            }

            const $row = $('<span class="d-flex justify-content-between align-items-center w-100"></span>');
            $row.append($('<span></span>').text(item.text));

            const dbId = $(item.element).data('id');
            if (dbId) {
                $row.append(
                    $('<i class="icon-trash text-danger ml-2 work-done-delete" title="Delete this option"></i>')
                        .attr('data-id', dbId)
                );
            } else if (item.newTag) {
                $row.append('<small class="text-muted ml-2">(new)</small>');
            }

            return $row;
        }

        $('#work_done').select2({
            width: '100%',
            multiple: true,
            tags: true,
            placeholder: $('#work_done').data('placeholder'),
            templateResult: workDoneTemplate,
            createTag: function(params) {
                const term = $.trim(params.term);
                if (!term) return null;
                return { id: term, text: term, newTag: true };
            }
        });

        // Delete an option from inside the dropdown (mouseup fires before
        // select2's own selection handler, so stop it from selecting).
        $(document).on('mouseup', '.work-done-delete', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $icon = $(this);
            const dbId = $icon.data('id');
            const $option = $('#work_done option').filter(function() {
                return String($(this).data('id')) === String(dbId);
            });
            const name = $option.first().text().trim();

            if (!confirm(`Delete "${name}" from the Work Done list?`)) {
                return;
            }

            $.ajax({
                url: `{{ url('vehicleMaintenances/work-dones') }}/${dbId}`,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' }
            }).done(function() {
                $option.remove();
                $('#work_done').trigger('change');
                $('#work_done').select2('close');
            }).fail(function() {
                alert('Could not delete the option. Please try again.');
            });
        });

        let warehouseProducts = [];

        function money(value) {
            return (Number(value) || 0).toFixed(2);
        }

        function qty(value) {
            return (Number(value) || 0).toString();
        }

        function productOptions(part = {}) {
            let options = '<option value="">--Select--</option>';
            let found = false;

            warehouseProducts.forEach(function(product) {
                const selected = String(product.id) === String(part.product_id) ? 'selected' : '';
                if (selected) found = true;
                const unit = product.unit_name || '';
                const stockLabel = `${qty(product.available_quantity)}${unit ? ' ' + unit : ''} available`;
                options += `<option value="${product.id}" data-price="${product.unit_price}" data-stock="${product.available_quantity}" data-unit="${unit}" ${selected}>${product.name} (${stockLabel})</option>`;
            });

            if (part.product_id && !found) {
                options += `<option value="${part.product_id}" data-price="${part.unit_price}" data-stock="0" data-unit="" selected>${part.product_name || 'Unknown Product'} (0 available - Previously Used)</option>`;
            }

            return options;
        }

        function reindexRows() {
            $('#parts-table tbody tr').each(function(index) {
                $(this).find('[data-field]').each(function() {
                    $(this).attr('name', `parts[${index}][${$(this).data('field')}]`);
                });
            });
        }

        function calculateAmount() {
            let total = Number($('#labor_cost').val()) || 0;
            $('#parts-table tbody tr').each(function() {
                const qty = Number($(this).find('.part-quantity').val()) || 0;
                const price = Number($(this).find('.part-unit-price').val()) || 0;
                const rowTotal = qty * price;
                $(this).find('.part-total').val(money(rowTotal));
                total += rowTotal;
            });
            $('#amount').val(money(total));
        }

        function addPartRow(part = {}) {
            const row = $(`
                <tr>
                    <td>
                        <select class="form-control part-product" data-field="product_id" required>
                            ${productOptions(part)}
                        </select>
                        <small class="text-muted stock-label"></small>
                    </td>
                    <td>
                        <input type="number" min="0.01" step="0.01" class="form-control part-quantity" data-field="quantity" value="${part.quantity || 1}" required>
                        <small class="text-muted qty-unit-label"></small>
                    </td>
                    <td><input type="number" min="0" step="0.01" class="form-control part-unit-price" data-field="unit_price" value="${part.unit_price || 0}" readonly></td>
                    <td><input type="number" class="form-control part-total" value="0.00" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-part-row">Remove</button></td>
                </tr>
            `);
            $('#parts-table tbody').append(row);
            row.find('.part-product').select2({ width: '100%' }).trigger('change', [true]);
            reindexRows();
            calculateAmount();
        }

        function loadWarehouseProducts(callback = null) {
            const warehouseId = $('#warehouse_id').val();
            warehouseProducts = [];
            $('#parts-table tbody').empty();

            if (!warehouseId) {
                calculateAmount();
                return;
            }

            $.get(`{{ url('vehicleMaintenances/warehouse') }}/${warehouseId}/products`, function(products) {
                warehouseProducts = products;
                const existing = window.existingMaintenanceParts || [];

                if (existing.length) {
                    existing.forEach(addPartRow);
                    window.existingMaintenanceParts = [];
                } else {
                    addPartRow();
                }

                if (callback) callback();
            });
        }

        function clearMileage(message = '') {
            $('#current_mileage').val('');
            $('#mileage-message').text(message);
        }

        function fetchDailyMileage() {
            const vehicleId = $('#vehicle_id').val();
            const serviceDate = $('#service_date').val();

            if (!vehicleId || !serviceDate) {
                clearMileage('');
                return;
            }

            clearMileage('');
            $.get(`{{ url('vehicleMaintenances/vehicle') }}/${vehicleId}/daily-mileage`, {
                service_date: serviceDate
            }).done(function(data) {
                $('#current_mileage').val(data.mileage ?? '');
                $('#mileage-message').text('');
                calculateAlertMileages();
            }).fail(function(xhr) {
                const message = xhr.responseJSON?.message || 'No mileage record found for the selected date.';
                clearMileage(message);
                calculateAlertMileages();
            });
        }

        $('#vehicle_id').on('change', function(e, isInitialLoad = false) {
            const vehicleId = $(this).val();
            if (!vehicleId) {
                $('#vehicle_make, #vehicle_model').val('');
                clearMileage('');
                return;
            }

            $.get(`{{ url('vehicleMaintenances/vehicle') }}/${vehicleId}/details`, function(data) {
                $('#vehicle_make').val(data.make || '');
                $('#vehicle_model').val(data.model || '');

                if (data.warehouse_id && !isInitialLoad) {
                    $('#warehouse_id').val(data.warehouse_id).trigger('change');
                }
            });

            fetchDailyMileage();
        });

        $('#service_date').on('change', fetchDailyMileage);
        $('#warehouse_id').on('change', loadWarehouseProducts);
        $('#add-part-row').on('click', function() { addPartRow(); });
        $('#labor_cost').on('input', calculateAmount);

        $('#parts-table').on('change', '.part-product', function(e, isInit = false) {
            const option = $(this).find(':selected');
            const row = $(this).closest('tr');
            if (!isInit && option.val()) {
                row.find('.part-unit-price').val(money(option.data('price')));
            }
            const unit = option.val() ? (option.data('unit') || '') : '';
            row.find('.stock-label').text(option.val() ? `${qty(option.data('stock'))}${unit ? ' ' + unit : ''} available` : '');
            row.find('.qty-unit-label').text(unit);
            calculateAmount();
        });

        $('#parts-table').on('input', '.part-quantity', calculateAmount);
        $('#parts-table').on('click', '.remove-part-row', function() {
            $(this).closest('tr').remove();
            reindexRows();
            calculateAmount();
        });

        if ($('#warehouse_id').val()) {
            loadWarehouseProducts();
        }
        if ($('#vehicle_id').val()) {
            $('#vehicle_id').trigger('change', [true]);
        }

        // ── Alert Integration ──────────────────────────────────────────────
        // When an alert is selected, auto-populate the read-only Threshold (KM) field.
        function applyAlertThreshold() {
            const selected = $('#alert_id').find(':selected');
            const threshold = selected.data('threshold');
            if (selected.val() && threshold !== undefined) {
                $('#threshold_km').val(threshold);
            } else {
                $('#threshold_km').val('');
            }
            calculateAlertMileages();
        }

        function calculateAlertMileages() {
            const currentKm = parseInt($('#current_mileage').val()) || 0;
            const thresholdKm = parseInt($('#threshold_km').val()) || 0;
            const alertBeforeKm = parseInt($('#alert_before_km').val()) || 0;

            if (currentKm > 0 && thresholdKm > 0) {
                const nextDue = currentKm + thresholdKm;
                $('#next_due_mileage').val(nextDue);

                if (alertBeforeKm > 0) {
                    $('#alert_start_mileage').val(nextDue - alertBeforeKm);
                } else {
                    $('#alert_start_mileage').val('');
                }
            } else {
                $('#next_due_mileage').val('');
                $('#alert_start_mileage').val('');
            }
        }

        $('#alert_id').on('change', applyAlertThreshold);
        $('#alert_before_km').on('input', calculateAlertMileages);

        // Seed threshold on page load (edit mode — alert already selected)
        applyAlertThreshold();
    });
</script>
