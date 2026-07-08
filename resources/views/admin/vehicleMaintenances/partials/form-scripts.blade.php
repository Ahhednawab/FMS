<script>
    $(function() {
        $('.select2').select2({ width: '100%' });

        let warehouseProducts = [];

        function money(value) {
            return (Number(value) || 0).toFixed(2);
        }

        function productOptions(selectedId = '') {
            let options = '<option value="">--Select--</option>';
            warehouseProducts.forEach(function(product) {
                const selected = String(product.id) === String(selectedId) ? 'selected' : '';
                options += `<option value="${product.id}" data-price="${product.unit_price}" data-stock="${product.available_quantity}" ${selected}>${product.name} (${product.available_quantity} available)</option>`;
            });
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
                            ${productOptions(part.product_id)}
                        </select>
                        <small class="text-muted stock-label"></small>
                    </td>
                    <td><input type="number" min="1" step="1" class="form-control part-quantity" data-field="quantity" value="${part.quantity || 1}" required></td>
                    <td><input type="number" min="0" step="0.01" class="form-control part-unit-price" data-field="unit_price" value="${part.unit_price || 0}" readonly></td>
                    <td><input type="number" class="form-control part-total" value="0.00" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-part-row">Remove</button></td>
                </tr>
            `);
            $('#parts-table tbody').append(row);
            row.find('.part-product').select2({ width: '100%' }).trigger('change');
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

        $('#vehicle_id').on('change', function() {
            const vehicleId = $(this).val();
            if (!vehicleId) {
                $('#vehicle_make, #vehicle_model, #current_mileage').val('');
                return;
            }

            $.get(`{{ url('vehicleMaintenances/vehicle') }}/${vehicleId}/details`, function(data) {
                $('#vehicle_make').val(data.make || '');
                $('#vehicle_model').val(data.model || '');
                $('#current_mileage').val(data.current_mileage || 0);

                if (data.warehouse_id) {
                    $('#warehouse_id').val(data.warehouse_id).trigger('change');
                }
            });
        });

        $('#warehouse_id').on('change', loadWarehouseProducts);
        $('#add-part-row').on('click', function() { addPartRow(); });
        $('#labor_cost').on('input', calculateAmount);

        $('#parts-table').on('change', '.part-product', function() {
            const option = $(this).find(':selected');
            const row = $(this).closest('tr');
            row.find('.part-unit-price').val(money(option.data('price')));
            row.find('.stock-label').text(option.val() ? `${option.data('stock')} available` : '');
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
            $('#vehicle_id').trigger('change');
        }
    });
</script>
