@extends('layouts.master')

@section('title', 'Railway Ticket Booking System')
@section('page_title', 'Railway Ticket Booking System')

@section('breadcrumb')
    <li class="breadcrumb-item active">Home</li>
@endsection

@section('content')
<!-- Main Welcome Banner -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <div class="mb-2">
                    <i class="fas fa-train fa-3x text-primary"></i>
                </div>
                <h2 class="font-weight-bold text-dark mb-1">Bangladesh Railway Online Reservation</h2>
                <p class="text-muted mx-auto mb-4" style="max-width: 600px;">
                    Search available trains, check departure times, view fares, and check seat availability across all major routes.
                </p>

                <!-- Embedded Search Bar -->
                <div class="card bg-light border p-3 text-left shadow-none mx-auto" style="max-width: 950px;">
                    <form method="GET" action="{{ route('trains.search') }}">
                        <div class="row align-items-end">
                            <div class="col-md-4 form-group mb-2">
                                <label class="font-weight-bold text-sm text-dark">
                                    <i class="fas fa-map-marker-alt text-danger mr-1"></i> From (Departure Station)
                                </label>
                                <select name="departure_station_id" class="form-control">
                                    <option value="">-- Choose Origin Station --</option>
                                    @if(isset($stations))
                                        @foreach($stations as $station)
                                            <option value="{{ $station->id }}">{{ $station->name }} ({{ $station->code }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-4 form-group mb-2">
                                <label class="font-weight-bold text-sm text-dark">
                                    <i class="fas fa-map-marker-alt text-success mr-1"></i> To (Arrival Station)
                                </label>
                                <select name="arrival_station_id" class="form-control">
                                    <option value="">-- Choose Destination Station --</option>
                                    @if(isset($stations))
                                        @foreach($stations as $station)
                                            <option value="{{ $station->id }}">{{ $station->name }} ({{ $station->code }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-2 form-group mb-2">
                                <label class="font-weight-bold text-sm text-dark">
                                    <i class="far fa-calendar-alt text-info mr-1"></i> Journey Date
                                </label>
                                <input type="date" name="journey_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" class="form-control">
                            </div>

                            <div class="col-md-2 form-group mb-2">
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm">
                                    <i class="fas fa-search mr-1"></i> Search Trains
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="mt-3">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm mr-2">
                                <i class="fas fa-tachometer-alt mr-1"></i> Admin Dashboard
                            </a>
                            <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-info btn-sm mr-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Manage Schedules
                            </a>
                        @else
                            <a href="{{ route('passenger.dashboard') }}" class="btn btn-outline-success btn-sm mr-2">
                                <i class="fas fa-tachometer-alt mr-1"></i> Passenger Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm mr-2">
                            <i class="fas fa-sign-in-alt mr-1"></i> Sign In
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-user-plus mr-1"></i> Register Free Account
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incremental Project Features / Modules Overview -->
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100 border-top border-primary" style="border-top-width: 4px !important;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded p-3 mr-3 shadow-xs">
                        <i class="fas fa-search fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-0">Route Search & Schedule</h5>
                        <span class="badge badge-primary">Real-Time</span>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    Search train routes, departure/arrival stations, transit times, and ticket fares with date-based filtering.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100 border-top border-success" style="border-top-width: 4px !important;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success text-white rounded p-3 mr-3 shadow-xs">
                        <i class="fas fa-chair fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-0">Interactive Seat Selection</h5>
                        <span class="badge badge-success">Live Availability</span>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    Real-time visual seat map across coaches (AC Berth, Snigdha, Shovon Chair) with live occupancy status.
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100 border-top border-info" style="border-top-width: 4px !important;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-info text-white rounded p-3 mr-3 shadow-xs">
                        <i class="fas fa-ticket-alt fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-weight-bold mb-0">Instant E-Ticket Vouchers</h5>
                        <span class="badge badge-info">Printable Slips</span>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    Secure transaction processing with unique PNR confirmation, cancellation workflows, and printable e-tickets.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
