@extends('layouts.master')

@section('title', 'Passenger Dashboard')
@section('page_title', 'Passenger Portal')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('passenger.dashboard') }}">Passenger</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Passenger Overview Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_bookings'] ?? 0 }}</h3>
                <p>Total Bookings</p>
            </div>
            <div class="icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <a href="#" class="small-box-footer">
                Booking History (Inc. 8) <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ $stats['upcoming_trips'] ?? 0 }}</h3>
                <p>Upcoming Trips</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="#" class="small-box-footer">
                View Tickets (Inc. 7) <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>{{ $stats['completed_trips'] ?? 0 }}</h3>
                <p>Completed Trips</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer">
                Trip Logs (Inc. 8) <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary shadow-sm">
            <div class="inner">
                <h3>{{ $stats['cancelled_trips'] ?? 0 }}</h3>
                <p>Cancelled Trips</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <a href="#" class="small-box-footer">
                Cancellations (Inc. 8) <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Passenger Quick Actions -->
<div class="row">
    <div class="col-md-7">
        <div class="card card-success card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-search mr-1"></i> Quick Train Search & Reservation
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Ready to travel? Search schedules between origin and destination stations, pick your preferred coach class, and select your seats.
                </p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> Train search, schedule filtering, and interactive seat booking will be available in <strong>Increments 5, 6, and 7</strong>.
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-user-circle mr-1"></i> Profile Summary
                </h3>
            </div>
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h3 class="profile-username text-center">{{ $user->name }}</h3>
                <p class="text-muted text-center"><span class="badge badge-success">Verified Passenger</span></p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Email Address</b> <span class="float-right text-muted">{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Phone Number</b> <span class="float-right text-muted">{{ $user->phone ?? 'Not provided' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Member Since</b> <span class="float-right text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Today' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
