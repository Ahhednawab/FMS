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

        // Products are fetched per warehouse and cached, so each row can use a
        // different warehouse without refetching the same list repeatedly.
        let productCache = {};
        let defaultWarehouseId = null;

        function money(value) {
            return (Number(value) || 0).toFixed(2);
        }

        function qty(value) {
            return (Number(value) || 0).toString();
        }

        function fetchProducts(warehouseId) {
            if (productCache[warehouseId]) {
                return $.Deferred().resolve(productCache[warehouseId]).promise();
            }

            return $.get(`{{ url('vehicleMaintenances/warehouse') }}/${warehouseId}/products`)
                .then(function(products) {
                    productCache[warehouseId] = products;
                    return products;
                });
        }

        function warehouseOptions(selectedId) {
            let options = '<option value="">--Select--</option>';

            (window.warehouseOptions || []).forEach(function(warehouse) {
                const selected = String(warehouse.id) === String(selectedId) ? 'selected' : '';
                options += `<option value="${warehouse.id}" ${selected}>${warehouse.name}</option>`;
            });

            return options;
        }

        function productOptions(products, part = {}) {
            let options = '<option value="">--Select--</option>';
            let found = false;

            products.forEach(function(product) {
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

        // Fill one row's Product dropdown from its own selected warehouse.
        function loadRowProducts($row, warehouseId, part = {}) {
            const $product = $row.find('.part-product');

            if (!warehouseId) {
                $product.html('<option value="">--Select warehouse first--</option>').trigger('change.select2');
                $row.find('.stock-label').text('');
                $row.find('.part-unit').val('');
                calculateAmount();
                return;
            }

            $product.html('<option value="">Loading…</option>').trigger('change.select2');

            fetchProducts(warehouseId).done(function(products) {
                $product.html(productOptions(products, part));
                // isInit = true so an existing row keeps its saved unit price.
                $product.trigger('change', [Boolean(part.product_id)]);
            });
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
            const rowWarehouse = part.warehouse_id || defaultWarehouseId || '';

            const row = $(`
                <tr>
                    <td>
                        <select class="form-control part-warehouse" data-field="warehouse_id" required>
                            ${warehouseOptions(rowWarehouse)}
                        </select>
                    </td>
                    <td>
                        <select class="form-control part-product" data-field="product_id" required>
                            <option value="">--Select warehouse first--</option>
                        </select>
                        <small class="text-muted stock-label"></small>
                    </td>
                    <td>
                        <input type="number" min="0.01" step="0.01" class="form-control part-quantity" data-field="quantity" value="${part.quantity || 1}" required>
                    </td>
                    <td><input type="text" class="form-control part-unit" value="" readonly></td>
                    <td><input type="number" min="0" step="0.01" class="form-control part-unit-price" data-field="unit_price" value="${part.unit_price || 0}" readonly></td>
                    <td><input type="number" class="form-control part-total" value="0.00" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-part-row">Remove</button></td>
                </tr>
            `);

            $('#parts-table tbody').append(row);
            row.find('.part-warehouse').select2({ width: '100%' });
            row.find('.part-product').select2({ width: '100%' });
            reindexRows();

            if (rowWarehouse) {
                loadRowProducts(row, rowWarehouse, part);
            }

            calculateAmount();
        }

        // Seed the table: existing rows on edit, otherwise one blank row.
        function seedPartRows() {
            const existing = window.existingMaintenanceParts || [];

            if (existing.length) {
                existing.forEach(function(part) { addPartRow(part); });
                window.existingMaintenanceParts = [];
            } else {
                addPartRow();
            }
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
                updatePredictivePreview();
            }).fail(function(xhr) {
                const message = xhr.responseJSON?.message || 'No mileage record found for the selected date.';
                clearMileage(message);
                updatePredictivePreview();
            });
        }

        $('#vehicle_id').on('change', function(e, isInitialLoad = false) {
            const vehicleId = $(this).val();
            if (!vehicleId) {
                $('#vehicle_make, #vehicle_model').val('');
                clearMileage('');
                configIntervals = { has_config: false, items: {} };
                updatePredictivePreview();
                return;
            }

            $.get(`{{ url('vehicleMaintenances/vehicle') }}/${vehicleId}/details`, function(data) {
                $('#vehicle_make').val(data.make || '');
                $('#vehicle_model').val(data.model || '');

                // The vehicle's station warehouse becomes the default for new
                // product rows; rows that already have one are left alone.
                defaultWarehouseId = data.warehouse_id || null;

                if (defaultWarehouseId && !isInitialLoad) {
                    $('#parts-table tbody tr').each(function() {
                        const $warehouse = $(this).find('.part-warehouse');
                        if (!$warehouse.val()) {
                            $warehouse.val(defaultWarehouseId).trigger('change');
                        }
                    });
                }
            });

            fetchConfigIntervals();
            fetchDailyMileage();
        });

        $('#service_date').on('change', fetchDailyMileage);
        $('#add-part-row').on('click', function() { addPartRow(); });
        $('#labor_cost').on('input', calculateAmount);

        // Changing a row's warehouse reloads only that row's product list.
        $('#parts-table').on('change', '.part-warehouse', function() {
            const row = $(this).closest('tr');
            row.find('.part-unit-price').val(0);
            row.find('.part-unit').val('');
            row.find('.stock-label').text('');
            loadRowProducts(row, $(this).val(), {});
        });

        $('#parts-table').on('change', '.part-product', function(e, isInit = false) {
            const option = $(this).find(':selected');
            const row = $(this).closest('tr');
            if (!isInit && option.val()) {
                row.find('.part-unit-price').val(money(option.data('price')));
            }
            const unit = option.val() ? (option.data('unit') || '') : '';
            row.find('.stock-label').text(option.val() ? `${qty(option.data('stock'))}${unit ? ' ' + unit : ''} available` : '');
            row.find('.part-unit').val(unit);
            calculateAmount();
        });

        $('#parts-table').on('input', '.part-quantity', calculateAmount);
        $('#parts-table').on('click', '.remove-part-row', function() {
            $(this).closest('tr').remove();
            reindexRows();
            calculateAmount();
        });

        // ── Predictive Maintenance ─────────────────────────────────────────
        let configIntervals = { has_config: false, items: {} };

        function currentMaintenanceType() {
            return $('#maintenance_type').val() || '';
        }

        function fmt(n) {
            return (Number(n) || 0).toLocaleString();
        }

        // Rebuild the Work Done option list to match the maintenance type:
        // predefined predictive items vs the free-text catalog. Current
        // selections (including custom "one-time" entries) are preserved.
        function rebuildWorkDoneOptions() {
            const $wd = $('#work_done');
            const selected = $wd.val() || [];
            const isPredictive = currentMaintenanceType() === 'predictive';

            $wd.find('option').remove();

            if (isPredictive) {
                (window.predictiveItems || []).forEach(function(name) {
                    $wd.append(new Option(name, name, false, selected.includes(name)));
                });
                $('#work-done-hint').text('Predictive: choose from the predefined items. Any custom entry is saved as one-time maintenance (no alerts).');
            } else {
                (window.workDoneCatalog || []).forEach(function(o) {
                    const opt = new Option(o.name, o.name, false, selected.includes(o.name));
                    opt.setAttribute('data-id', o.id);
                    $wd.append(opt);
                });
                $('#work-done-hint').text('');
            }

            // Preserve any selected values not present in the rebuilt list.
            selected.forEach(function(name) {
                if (!$wd.find('option').toArray().some(o => o.value === name)) {
                    $wd.append(new Option(name, name, true, true));
                }
            });

            $wd.val(selected).trigger('change.select2');
        }

        function fetchConfigIntervals() {
            const vehicleId = $('#vehicle_id').val();
            if (!vehicleId) {
                configIntervals = { has_config: false, items: {} };
                updatePredictivePreview();
                return;
            }
            $.get(`{{ url('vehicleMaintenances/vehicle') }}/${vehicleId}/config-intervals`, function(data) {
                configIntervals = data || { has_config: false, items: {} };
                updatePredictivePreview();
            });
        }

        // Show the auto-calculated next-due / upcoming-alert mileages for each
        // selected item while the record is being entered.
        function updatePredictivePreview() {
            const $row = $('#predictive-preview-row');

            if (currentMaintenanceType() !== 'predictive') {
                $row.hide();
                return;
            }

            const current = parseInt($('#current_mileage').val()) || 0;
            const selected = $('#work_done').val() || [];
            const predictive = window.predictiveItems || [];
            const rows = [];

            selected.forEach(function(name) {
                if (predictive.includes(name)) {
                    const interval = configIntervals.items ? configIntervals.items[name] : null;
                    if (interval && current > 0) {
                        rows.push(`<strong>${name}</strong> — next due at <strong>${fmt(current + interval)} KM</strong> `
                            + `(interval ${fmt(interval)} KM, upcoming alert at ${fmt(current + interval - 200)} KM)`);
                    } else if (interval) {
                        rows.push(`<strong>${name}</strong> — interval ${fmt(interval)} KM (mileage not available yet)`);
                    } else {
                        rows.push(`<strong>${name}</strong> — no interval configured for this Make/Model`);
                    }
                } else {
                    rows.push(`<strong>${name}</strong> — one-time maintenance (no alerts)`);
                }
            });

            if (!rows.length) {
                $row.hide();
                return;
            }

            let html = '';
            if (!configIntervals.has_config) {
                html += `<div class="text-danger mb-2">No maintenance configuration found for this vehicle's Make/Model. `
                    + `Predefined items will not generate alerts until a configuration is added.</div>`;
            }
            html += rows.map(r => `<div>• ${r}</div>`).join('');
            $('#predictive-preview-body').html(html);
            $row.show();
        }

        $('#maintenance_type').on('change', function() {
            rebuildWorkDoneOptions();
            updatePredictivePreview();
        });

        $('#work_done').on('change', updatePredictivePreview);

        // ── Initial state ──────────────────────────────────────────────────
        rebuildWorkDoneOptions();
        seedPartRows();

        if ($('#vehicle_id').val()) {
            $('#vehicle_id').trigger('change', [true]);
        } else {
            updatePredictivePreview();
        }
    });
</script>
