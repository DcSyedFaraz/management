@extends('layout.app')
@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                        <div class="d-flex align-items-end row">
                            <div class="col-12">
                                <div class="card-body">
                                    <h5>{{ isset($user) ? 'Edit User' : 'Create User' }}</h5>
                                    <!-- Use the same form for both create and edit -->
                                    <form method="POST"
                                        action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}">
                                        @csrf
                                        @if (isset($user))
                                            @method('PUT') <!-- Method spoofing for PUT -->
                                        @endif

                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label for="name">Name</label>
                                                <input type="text" class="form-control" name="name" placeholder="Name"
                                                    value="{{ old('name', isset($user) ? $user->name : '') }}" required>
                                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                            </div>

                                            <div class="col-6">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" name="email"
                                                    placeholder="Email"
                                                    value="{{ old('email', isset($user) ? $user->email : '') }}" required
                                                    autocomplete="new-password">
                                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label for="password">Password</label>
                                                <input type="password" class="form-control" name="password"
                                                    placeholder="Password" {{ isset($user) ? '' : 'required' }}
                                                    autocomplete="new-password">
                                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                                <!-- Only require password for new user -->
                                                @if (isset($user))
                                                    <small class="text-muted">Leave blank to keep current password</small>
                                                @endif
                                            </div>

                                            <div class="col-6">
                                                <label for="password_confirmation">Confirm Password</label>
                                                <input type="password" class="form-control" name="password_confirmation"
                                                    placeholder="Confirm Password" {{ isset($user) ? '' : 'required' }}
                                                    autocomplete="new-password">
                                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label for="role">Role</label>
                                                <select name="roles" id="role" class="form-select my-2" required>
                                                    <option value="">Select role</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}"
                                                            {{ isset($user) && $user->roles->contains('name', $role->name) ? 'selected' : '' }}>
                                                            {{ $role->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            {{ isset($user) ? 'Update' : 'Save' }}
                                        </button>
                                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
