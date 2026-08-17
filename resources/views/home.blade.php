@extends('layouts.master')

@section('title', 'Railway Ticket Booking System')
@section('page_title', 'Railway Ticket Booking System')

@section('breadcrumb')
    <li class="breadcrumb-item active">Home</li>
@endsection

@section('content')
<div class="row">
    <!-- Main Welcome Banner -->
    <div class="col-12">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-train fa-4x text-primary"></i>
                </div>
                <h2 class="font-weight-bold text-dark mb-2">Welcome to Bangladesh Railway Online Booking</h2>
                <p class="lead text-muted mx-auto" style="max-width: 650px;">
                    Fast, reliable, and convenient online railway reservation system. Search available trains, view schedules, select seats, and manage your journey seamlessly.
                </p>
                <div class="mt-4">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg mr-2">
                                <i class="fas fa-tachometer-alt mr-1"></i> Admin Dashboard
                            </a>
                        @else
                            <a href="{{ route('passenger.dashboard') }}" class="btn btn-success btn-lg mr-2">
                                <i class="fas fa-tachometer-alt mr-1"></i> Passenger Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg mr-2">
                            <i class="fas fa-sign-in-alt mr-1"></i> Sign In to Book
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-user-plus mr-1"></i> Create Free Account
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incremental Project Features / Modules Overview -->
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded p-3 mr-3">
                        <i class="fas fa-search fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-0">Train Search & Schedule</h5>
                        <small class="text-muted">Increment 5</small>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    Search train routes, departure/arrival stations, transit times, and ticket fares with date-based filtering.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success text-white rounded p-3 mr-3">
                        <i class="fas fa-chair fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-0">Interactive Seat Selection</h5>
                        <small class="text-muted">Increment 6</small>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    Real-time visual seat map across coaches (AC, Shovon, Chair) with live occupancy status.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-info text-white rounded p-3 mr-3">
                        <i class="fas fa-ticket-alt fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-0">Instant Ticket Generation</h5>
                        <small class="text-muted">Increment 7</small>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    Secure transaction processing with unique PNR confirmation and printable e-ticket vouchers.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
