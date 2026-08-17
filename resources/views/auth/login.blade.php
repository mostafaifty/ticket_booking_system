@extends('layouts.auth')

@section('title', 'Sign In')
@section('body_class', 'login-page')
@section('box_class', 'login-box')

@section('content')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header text-center bg-white">
        <h4 class="mb-0 font-weight-bold">Sign In</h4>
        <small class="text-muted">Access your account or administrative portal</small>
    </div>
    <div class="card-body login-card-body">
        <form action="{{ route('login') }}" method="post">
            @csrf

            <div class="input-group mb-3">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
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
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required>
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

            <div class="row align-items-center mb-3">
                <div class="col-8">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">
                            Remember Me
                        </label>
                    </div>
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt mr-1"></i> Sign In
                    </button>
                </div>
            </div>
        </form>

        <hr>

        <div class="text-center">
            <p class="mb-1 text-sm">
                Don't have an account? <a href="{{ route('register') }}" class="text-primary font-weight-bold">Register as Passenger</a>
            </p>
            <p class="mb-0 text-sm">
                <a href="{{ route('home') }}" class="text-secondary"><i class="fas fa-arrow-left mr-1"></i> Back to Homepage</a>
            </p>
        </div>
    </div>
    <!-- /.login-card-body -->
</div>
@endsection
