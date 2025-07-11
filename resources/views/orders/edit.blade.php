@extends('layout.app')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <h5>Edit Order</h5>
                <form method="POST" action="{{ route('orders.update', $order->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" name="status" class="form-control" value="{{ old('status', $order->status) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Dispatch</label>
                        <input type="date" name="last_dispatch" class="form-control" value="{{ old('last_dispatch', optional($order->last_dispatch)->toDateString()) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Residence</label>
                        <input type="text" name="residence" class="form-control" value="{{ old('residence', $order->residence) }}">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="reuseable_bed_protection" value="1" {{ old('reuseable_bed_protection', $order->reuseable_bed_protection) ? 'checked' : '' }}>
                        <label class="form-check-label">Reusable Bed Protection</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Beantrager</label>
                        <input type="text" name="beantrager" class="form-control" value="{{ old('beantrager', $order->beantrager) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sign</label>
                        <input type="text" name="sign" class="form-control" value="{{ old('sign', $order->sign) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Geburtsdatum</label>
                        <input type="date" name="geburtsdatum" class="form-control" value="{{ old('geburtsdatum', $order->geburtsdatum) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Versicherter (JSON)</label>
                        <textarea name="versicherter" class="form-control" rows="3">{{ old('versicherter', json_encode($order->versicherter, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address (JSON)</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', json_encode($order->address, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Antragsteller (JSON)</label>
                        <textarea name="antragsteller" class="form-control" rows="3">{{ old('antragsteller', json_encode($order->antragsteller, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Insurance Type</label>
                        <input type="text" name="insuranceType" class="form-control" value="{{ old('insuranceType', $order->insuranceType) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Insurance Provider</label>
                        <input type="text" name="insuranceProvider" class="form-control" value="{{ old('insuranceProvider', $order->insuranceProvider) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Insurance Number</label>
                        <input type="text" name="insuranceNumber" class="form-control" value="{{ old('insuranceNumber', $order->insuranceNumber) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pflegegrad</label>
                        <input type="text" name="pflegegrad" class="form-control" value="{{ old('pflegegrad', $order->pflegegrad) }}">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="changeProvider" value="1" {{ old('changeProvider', $order->changeProvider) ? 'checked' : '' }}>
                        <label class="form-check-label">Change Provider</label>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="requestBedPads" value="1" {{ old('requestBedPads', $order->requestBedPads) ? 'checked' : '' }}>
                        <label class="form-check-label">Request Bed Pads</label>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="isSameAsContact" value="1" {{ old('isSameAsContact', $order->isSameAsContact) ? 'checked' : '' }}>
                        <label class="form-check-label">Is Same As Contact</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery Address</label>
                        <input type="text" name="deliveryAddress" class="form-control" value="{{ old('deliveryAddress', $order->deliveryAddress) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Application Receipt</label>
                        <input type="text" name="applicationReceipt" class="form-control" value="{{ old('applicationReceipt', $order->applicationReceipt) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Awareness Source</label>
                        <input type="text" name="awarenessSource" class="form-control" value="{{ old('awarenessSource', $order->awarenessSource) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Consultation Check</label>
                        <input type="number" name="consultation_check" class="form-control" value="{{ old('consultation_check', $order->consultation_check) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Products (JSON)</label>
                        <textarea name="products" class="form-control" rows="3">{{ old('products', json_encode($order->products, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dispatch Months (JSON)</label>
                        <textarea name="dispatch_months" class="form-control" rows="3">{{ old('dispatch_months', json_encode($order->dispatch_months, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Order</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back</a>
                </form>
                <hr>
                <h5>Connected Users</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($connectedUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <form method="POST" action="{{ route('orders.detach_user', [$order->id, $user->id]) }}" onsubmit="return confirm('Delink user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delink</button>
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
@endsection
