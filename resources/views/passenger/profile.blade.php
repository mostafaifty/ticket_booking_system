@extends('layouts.master')

@section('title', 'My Profile')
@section('page_title', 'Passenger Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('passenger.dashboard') }}">Passenger</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="row">
    <!-- Left Column: User Summary Card -->
    <div class="col-md-4">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h3 class="profile-username text-center font-weight-bold">{{ $user->name }}</h3>
                <p class="text-muted text-center">
                    <span class="badge badge-success px-3 py-1 text-sm font-weight-normal">
                        <i class="fas fa-check-circle mr-1"></i> {{ ucfirst($user->role) }}
                    </span>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b><i class="fas fa-envelope text-muted mr-1"></i> Email</b>
                        <span class="float-right text-dark">{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-phone text-muted mr-1"></i> Phone</b>
                        <span class="float-right text-dark">{{ $user->phone ?? 'Not provided' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-ticket-alt text-muted mr-1"></i> Total Bookings</b>
                        <span class="float-right badge badge-primary badge-pill">{{ $user->bookings()->count() }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-calendar-alt text-muted mr-1"></i> Member Since</b>
                        <span class="float-right text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</span>
                    </li>
                </ul>

                <a href="{{ route('passenger.dashboard') }}" class="btn btn-outline-primary btn-block">
                    <i class="fas fa-tachometer-alt mr-1"></i> Back to Dashboard
                </a>
            </div>
            <!-- /.card-body -->
        </div>
    </div>

    <!-- Right Column: Settings / Edit Tabs -->
    <div class="col-md-8">
        <div class="card card-primary card-outline card-tabs shadow-sm">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="profile-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-info" data-toggle="pill" href="#pane-info" role="tab" aria-controls="pane-info" aria-selected="true">
                            <i class="fas fa-user-edit mr-1"></i> Edit Profile Info
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-password" data-toggle="pill" href="#pane-password" role="tab" aria-controls="pane-password" aria-selected="false">
                            <i class="fas fa-key mr-1"></i> Change Password
                        </a>
                    </li>
                </ul>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="tab-content" id="profile-tab-content">
                    
                    <!-- TAB 1: Edit Profile Information -->
                    <div class="tab-pane fade show active" id="pane-info" role="tabpanel" aria-labelledby="tab-info">
                        <form action="{{ route('passenger.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="name"><i class="fas fa-user mr-1 text-muted"></i> Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope mr-1 text-muted"></i> Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone mr-1 text-muted"></i> Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+8801XXXXXXXXX">
                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save Changes
                            </button>
                        </form>
                    </div>

                    <!-- TAB 2: Change Password -->
                    <div class="tab-pane fade" id="pane-password" role="tabpanel" aria-labelledby="tab-password">
                        <form action="{{ route('passenger.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="current_password"><i class="fas fa-lock mr-1 text-muted"></i> Current Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                                @error('current_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password"><i class="fas fa-key mr-1 text-muted"></i> New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                <small class="form-text text-muted">Minimum 6 characters.</small>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation"><i class="fas fa-check-double mr-1 text-muted"></i> Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-shield-alt mr-1"></i> Update Password
                            </button>
                        </form>
                    </div>

                </div>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>
@endsection
