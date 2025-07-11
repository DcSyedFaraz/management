@extends('layout.app')
@section('content')
    @php
        // Ensure JSON fields are arrays, not strings
        $versicherter = is_string($order->versicherter)
            ? json_decode($order->versicherter, true)
            : $order->versicherter ?? [];
        $address = is_string($order->address) ? json_decode($order->address, true) : $order->address ?? [];
        $antragsteller = is_string($order->antragsteller)
            ? json_decode($order->antragsteller, true)
            : $order->antragsteller ?? [];
        $products = is_string($order->products) ? json_decode($order->products, true) : $order->products ?? [];
        $dispatch_months = is_string($order->dispatch_months)
            ? json_decode($order->dispatch_months, true)
            : $order->dispatch_months ?? [];
        $entries = [];
        foreach (old('product_ids', array_keys($products)) as $i => $id) {
            $entries[] = [
                'key' => $id,
                'amount' => old('product_amounts.' . $i, data_get($products, $id . '.amount', '')),
            ];
        }
    @endphp

    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-12">
                    {{-- Header --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="mdi mdi-file-edit me-2"></i>Edit Order</h4>
                            <div class="btn-group">
                                <button type="submit" form="order-form" class="btn btn-primary">
                                    <i class="mdi mdi-check me-1"></i>Update Order
                                </button>
                                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                                    <i class="mdi mdi-arrow-left me-1"></i>Back
                                </a>
                            </div>
                        </div>
                    </div>

                    <form id="order-form" method="POST" action="{{ route('orders.update', $order->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- Basic Information --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="mdi mdi-information-outline me-2"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <input type="text" name="status" class="form-control form-control-sm"
                                            value="{{ old('status', $order->status) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Last Dispatch</label>
                                        <input type="date" name="last_dispatch" class="form-control form-control-sm"
                                            value="{{ old('last_dispatch', $order->last_dispatch?->toDateString()) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Residence</label>
                                        <input type="text" name="residence" class="form-control form-control-sm"
                                            value="{{ old('residence', $order->residence) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Beantrager</label>
                                        <input type="text" name="beantrager" class="form-control form-control-sm"
                                            value="{{ old('beantrager', $order->beantrager) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Sign</label>
                                        <input type="text" name="sign" class="form-control form-control-sm"
                                            value="{{ old('sign', $order->sign) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Geburtsdatum</label>
                                        <input type="date" name="geburtsdatum" class="form-control form-control-sm"
                                            value="{{ old('geburtsdatum', $order->geburtsdatum?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Pflegegrad</label>
                                        <input type="text" name="pflegegrad" class="form-control form-control-sm"
                                            value="{{ old('pflegegrad', $order->pflegegrad) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Personal Information --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="mdi mdi-account-group me-2"></i>Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    {{-- Versicherter --}}
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="text-primary mb-3">Versicherter</h6>
                                            <div class="row g-2">
                                                @foreach (['anrede', 'titel', 'vorname', 'nachname'] as $field)
                                                    <div class="col-6">
                                                        <label class="form-label small">{{ ucfirst($field) }}</label>
                                                        <input type="text" name="versicherter[{{ $field }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("versicherter.$field", $versicherter[$field] ?? '') }}">
                                                    </div>
                                                @endforeach
                                                @foreach (['strasse', 'stadt', 'plz', 'land', 'email', 'telefon'] as $field)
                                                    <div class="col-12">
                                                        <label class="form-label small">{{ ucfirst($field) }}</label>
                                                        <input type="text" name="versicherter[{{ $field }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("versicherter.$field", $versicherter[$field] ?? '') }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Address --}}
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="text-success mb-3">Address</h6>
                                            <div class="row g-2">
                                                @foreach (['strasse', 'stadt', 'plz', 'land', 'email', 'telefon'] as $field)
                                                    <div class="col-12">
                                                        <label class="form-label small">{{ ucfirst($field) }}</label>
                                                        <input type="text" name="address[{{ $field }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("address.$field", $address[$field] ?? '') }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Antragsteller --}}
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="text-warning mb-3">Antragsteller</h6>
                                            <div class="row g-2">
                                                @foreach (['anrede', 'titel', 'vorname', 'nachname'] as $field)
                                                    <div class="col-6">
                                                        <label class="form-label small">{{ ucfirst($field) }}</label>
                                                        <input type="text" name="antragsteller[{{ $field }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("antragsteller.$field", $antragsteller[$field] ?? '') }}">
                                                    </div>
                                                @endforeach
                                                @foreach (['strasse', 'stadt', 'plz', 'land', 'email', 'telefon'] as $field)
                                                    <div class="col-12">
                                                        <label class="form-label small">{{ ucfirst($field) }}</label>
                                                        <input type="text" name="antragsteller[{{ $field }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("antragsteller.$field", $antragsteller[$field] ?? '') }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Insurance Information --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="mdi mdi-shield-account me-2"></i>Insurance Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Insurance Type</label>
                                        <input type="text" name="insuranceType" class="form-control form-control-sm"
                                            value="{{ old('insuranceType', $order->insuranceType) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Insurance Provider</label>
                                        <input type="text" name="insuranceProvider"
                                            class="form-control form-control-sm"
                                            value="{{ old('insuranceProvider', $order->insuranceProvider) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Insurance Number</label>
                                        <input type="text" name="insuranceNumber" class="form-control form-control-sm"
                                            value="{{ old('insuranceNumber', $order->insuranceNumber) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Delivery Address</label>
                                        <input type="text" name="deliveryAddress" class="form-control form-control-sm"
                                            value="{{ old('deliveryAddress', $order->deliveryAddress) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Application Receipt</label>
                                        <input type="text" name="applicationReceipt"
                                            class="form-control form-control-sm"
                                            value="{{ old('applicationReceipt', $order->applicationReceipt) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Awareness Source</label>
                                        <input type="text" name="awarenessSource" class="form-control form-control-sm"
                                            value="{{ old('awarenessSource', $order->awarenessSource) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="mdi mdi-cog me-2"></i>Options</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input"
                                                name="reuseable_bed_protection" value="1"
                                                {{ old('reuseable_bed_protection', $order->reuseable_bed_protection) ? 'checked' : '' }}>
                                            <label class="form-check-label">Reusable Bed Protection</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="changeProvider"
                                                value="1"
                                                {{ old('changeProvider', $order->changeProvider) ? 'checked' : '' }}>
                                            <label class="form-check-label">Change Provider</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="requestBedPads"
                                                value="1"
                                                {{ old('requestBedPads', $order->requestBedPads) ? 'checked' : '' }}>
                                            <label class="form-check-label">Request Bed Pads</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="isSameAsContact"
                                                value="1"
                                                {{ old('isSameAsContact', $order->isSameAsContact) ? 'checked' : '' }}>
                                            <label class="form-check-label">Is Same As Contact</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="consultation_check"
                                                value="1"
                                                {{ old('consultation_check', $order->consultation_check) ? 'checked' : '' }}>
                                            <label class="form-check-label">Consultation Check</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Products --}}
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="mdi mdi-package-variant me-2"></i>Products</h6>
                                <button type="button" id="add-product" class="btn btn-outline-primary btn-sm">
                                    <i class="mdi mdi-plus me-1"></i>Add Product
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="products-wrapper">
                                    @foreach ($entries as $i => $entry)
                                        <div class="product-item mb-2">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Key</span>
                                                <input type="text" name="product_ids[]" class="form-control"
                                                    value="{{ $entry['key'] }}">
                                                <span class="input-group-text">Amount</span>
                                                <input type="number" name="product_amounts[]" class="form-control"
                                                    value="{{ $entry['amount'] }}">
                                                <button type="button" class="btn btn-outline-danger remove-product">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Hidden Template --}}
                        <div class="product-item mb-2 d-none" id="product-template">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Key</span>
                                <input type="text" data-name="product_ids[]" class="form-control">
                                <span class="input-group-text">Amount</span>
                                <input type="number" data-name="product_amounts[]" class="form-control">
                                <button type="button" class="btn btn-outline-danger remove-product">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Dispatch Months --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="mdi mdi-calendar-month me-2"></i>Dispatch Months</h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $months = [
                                        1 => 'Jan',
                                        2 => 'Feb',
                                        3 => 'Mar',
                                        4 => 'Apr',
                                        5 => 'May',
                                        6 => 'Jun',
                                        7 => 'Jul',
                                        8 => 'Aug',
                                        9 => 'Sep',
                                        10 => 'Oct',
                                        11 => 'Nov',
                                        12 => 'Dec',
                                    ];
                                    $selected = old('dispatch_months', $dispatch_months);
                                @endphp
                                <div class="row g-2">
                                    @foreach ($months as $num => $label)
                                        <div class="col-md-2 col-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="dispatch_months[]"
                                                    value="{{ $num }}"
                                                    {{ in_array($num, $selected) ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Connected Users --}}
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="mdi mdi-account-multiple me-2"></i>Connected Users</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th width="100">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($connectedUsers as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <form method="POST"
                                                        action="{{ route('orders.detach_user', [$order->id, $user->id]) }}"
                                                        onsubmit="return confirm('Delink user?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="mdi mdi-link-off"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom Styles --}}
@section('styles')
    <script src="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/scripts/verify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <style>
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.2s ease-in-out;
        }

        .card:hover {
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .form-control-sm {
            font-size: 0.875rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .form-label.small {
            font-size: 0.75rem;
            margin-bottom: 0.125rem;
        }

        .product-item {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group-text {
            font-size: 0.75rem;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.5rem;
        }

        .border {
            border-color: #e9ecef !important;
        }

        .text-primary {
            color: #0d6efd !important;
        }

        .text-success {
            color: #198754 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }
    </style>
@endsection

{{-- Enhanced Script --}}
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let productIndex = {{ count($entries) }};

            // Add new product with animation
            document.getElementById('add-product').addEventListener('click', function() {
                const tmpl = document.getElementById('product-template');
                const clone = tmpl.cloneNode(true);
                clone.id = '';
                clone.classList.remove('d-none');

                // Update form field names
                clone.querySelectorAll('[data-name]').forEach(el => {
                    const name = el.getAttribute('data-name');
                    el.setAttribute('name', name);
                    el.removeAttribute('data-name');
                    el.value = '';
                });

                // Add remove functionality
                clone.querySelector('.remove-product').addEventListener('click', function() {
                    clone.style.animation = 'fadeOut 0.3s ease-in-out';
                    setTimeout(() => clone.remove(), 300);
                });

                document.getElementById('products-wrapper').appendChild(clone);
                productIndex++;
            });

            // Remove existing products with animation
            document.querySelectorAll('.remove-product').forEach(btn => {
                btn.addEventListener('click', function() {
                    const item = btn.closest('.product-item');
                    item.style.animation = 'fadeOut 0.3s ease-in-out';
                    setTimeout(() => item.remove(), 300);
                });
            });

            // Add fadeOut animation
            const style = document.createElement('style');
            style.textContent = `
                    @keyframes fadeOut {
                        from { opacity: 1; transform: translateY(0); }
                        to { opacity: 0; transform: translateY(-10px); }
                    }
                `;
            document.head.appendChild(style);
        });
    </script>
@endsection
@endsection
