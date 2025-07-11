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
