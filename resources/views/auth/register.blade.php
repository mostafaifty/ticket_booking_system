@extends('layouts.auth')

@section('title', 'Passenger Registration')
@section('body_class', 'register-page')
@section('box_class', 'register-box')

@section('content')
<div class="card card-outline card-success shadow-sm">
    <div class="card-header text-center bg-white">
        <h4 class="mb-0 font-weight-bold">Create Account</h4>
        <small class="text-muted">Register as a Passenger to book train tickets</small>
    </div>
    <div class="card-body register-card-body">
        <form action="{{ route('register') }}" method="post">
            @csrf

            <div class="input-group mb-3">
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Full Name" value="{{ old('name') }}" required autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-user"></span>
                    </div>
                </div>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="input-group mb-3">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" value="{{ old('email') }}" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="input-group mb-3">
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Phone Number (Optional)" value="{{ old('phone') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-phone"></span>
                    </div>
                </div>
                @error('phone')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password (min 6 characters)" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="input-group mb-3">
                <input type="password" name="password_confirmation" class="form-control" placeholder="Retype password" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-user-plus mr-1"></i> Register Account
                    </button>
                </div>
            </div>
        </form>

        <hr>

        <div class="text-center">
            <p class="mb-1 text-sm">
                Already have an account? <a href="{{ route('login') }}" class="text-success font-weight-bold">Sign In</a>
            </p>
            <p class="mb-0 text-sm">
                <a href="{{ route('home') }}" class="text-secondary"><i class="fas fa-arrow-left mr-1"></i> Back to Homepage</a>
            </p>
        </div>
    </div>
    <!-- /.form-box -->
</div>
@endsection
