@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>Vehicle Maintenance Issue</h4>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('maintainer.createjobcart') }}">
                    @csrf

                    {{-- Vehicle Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-control" required>
                            <option value="">Select Vehicle</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->vehicle_no ?? 'Vehicle #' . $vehicle->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Issue Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label">Issue <span class="text-danger">*</span></label>
                        <select name="issue_id" class="form-control" required>
                            <option value="">Select Issue</option>
                            @foreach ($issues as $issue)
                                <option value="{{ $issue->id }}">
                                    {{ $issue->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Product Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <select id="inventorySelect" class="form-control mr-2">
                                <option value="">Select Product</option>
                                @foreach ($inventory as $item)
                                    <option value="{{ $item->id ?? '' }}" data-value="{{ $item->name ?? '' }}">
                                        {{ $item->name ?? 'Product #' . $item->id }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button" class="btn btn-primary" id="addInventory">
                                Add
                            </button>
                        </div>
                    </div>

                    {{-- Added Products Fields --}}
                    <div id="inventoryContainer"></div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="open request">Open Request</option>
                            <option value="in progress">In Progress</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>

                    {{-- Maintenance Type --}}
                    <div class="mb-3">
                        <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="corrective">Corrective</option>
                            <option value="breakdown">Breakdown</option>
                            <option value="preventive">Preventive</option>
                        </select>
                    </div>

                    {{-- Remarks --}}
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="4"></textarea>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-success">
                        Submit
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Add product (same as before)
            $('#addInventory').on('click', function() {
                var $select = $('#inventorySelect');
                var $option = $select.find(':selected');

                if (!$option.val() || !$option.data('value')) return;

                var itemId = $option.val().trim();
                var itemName = $option.data('value').trim();

                var $container = $('#inventoryContainer');

                if ($container.find('[data-id="' + itemId + '"]').length) {
                    alert('This product is already added');
                    return;
                }

                var $row = $('<div>', {
                    class: 'row mb-2 align-items-center',
                    'data-id': itemId
                });

                var $colName = $('<div>', {
                    class: 'col-md-6'
                }).append(
                    $('<input>', {
                        type: 'text',
                        class: 'form-control',
                        value: itemName,
                        disabled: true
                    })
                );

                var $colQty = $('<div>', {
                    class: 'col-md-4'
                }).append(
                    $('<input>', {
                        type: 'number',
                        name: 'inventory[' + itemId + '][qty]',
                        class: 'form-control',
                        min: 1,
                        step: 1,
                        placeholder: 'Quantity',
                        required: true
                    })
                );

                var $colRemove = $('<div>', {
                    class: 'col-md-2'
                }).append(
                    $('<button>', {
                        type: 'button',
                        class: 'btn btn-danger btn-sm remove-item',
                        text: '×'
                    })
                );

                $row.append($colName, $colQty, $colRemove);
                $container.append($row);

                $select.val('');
            });

            // Remove product
            $(document).on('click', '.remove-item', function() {
                $(this).closest('.row').remove();
            });

            // AJAX form submit
            $('form').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);

                // Validate: at least one product
                if ($('#inventoryContainer').find('input[name^="inventory"]').length === 0) {
                    alert('Please add at least one product.');
                    return;
                }

                var formData = $form.serialize(); // Serialize all form data


                $.ajax({
                    url: $form.attr('action'),
                    method: $form.attr('method'),
                    data: formData,
                    success: function(response) {
                        // Success feedback
                        alert('Job cart created successfully!');
                        // Optional: reset form
                        $form[0].reset();
                        $('#inventoryContainer').empty();
                    },
                    error: function(xhr) {
                        // Error feedback
                        var errors = xhr.responseJSON?.errors;
                        if (errors) {
                            var messages = Object.values(errors).flat().join('\n');
                            alert('Validation Error:\n' + messages);
                        } else {
                            alert('Something went wrong!');
                        }
                    }
                });
            });
        });
    </script>
@endpush
