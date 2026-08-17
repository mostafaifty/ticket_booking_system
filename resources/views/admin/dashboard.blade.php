@extends('layouts.master')

@section('title', 'Admin Dashboard')
@section('page_title', 'Administrative Overview')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Row 1: Fleet, Network & Schedule Metrics -->
<div class="row">
    <!-- Total Passengers -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-indigo shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_passengers'] ?? 0 }}</h3>
                <p>Total Passengers</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="small-box-footer">
                Passenger Profiles <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Trains -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_trains'] ?? 0 }}</h3>
                <p>Total Trains</p>
            </div>
            <div class="icon">
                <i class="fas fa-subway"></i>
            </div>
            <a href="#quick-links-card" class="small-box-footer">
                Manage Trains <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Stations -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_stations'] ?? 0 }}</h3>
                <p>Total Stations</p>
            </div>
            <div class="icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <a href="{{ route('trains.search') }}" class="small-box-footer">
                Station Network <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Schedules -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_schedules'] ?? 0 }}</h3>
                <p>Total Schedules</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="{{ route('admin.schedules.index') }}" class="small-box-footer">
                Manage Schedules <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Row 2: Booking Performance Metrics -->
<div class="row">
    <!-- Total Bookings -->
    <div class="col-lg-4 col-12">
        <div class="small-box bg-purple shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_bookings'] ?? 0 }}</h3>
                <p>Total Bookings</p>
            </div>
            <div class="icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="small-box-footer">
                View All Bookings <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Confirmed Bookings -->
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ $stats['confirmed_bookings'] ?? 0 }}</h3>
                <p>Confirmed Bookings</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.bookings.index', ['status' => 'confirmed']) }}" class="small-box-footer">
                Confirmed Trips <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Cancelled Bookings -->
    <div class="col-lg-4 col-6">
        <div class="small-box bg-danger shadow-sm">
            <div class="inner">
                <h3>{{ $stats['cancelled_bookings'] ?? 0 }}</h3>
                <p>Cancelled Bookings</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <a href="{{ route('admin.bookings.index', ['status' => 'cancelled']) }}" class="small-box-footer">
                Cancelled Tickets <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Quick Links & Quick Actions Panel -->
<div class="row mb-4" id="quick-links-card">
    <div class="col-12">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-bolt text-warning mr-1"></i> Quick Management Actions
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Manage Trains -->
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <button type="button" class="btn btn-outline-info btn-block py-3 font-weight-bold shadow-sm h-100" data-toggle="modal" data-target="#manageSeatsModal">
                            <i class="fas fa-subway fa-2x d-block mb-2 text-info"></i>
                            Manage Trains
                        </button>
                    </div>

                    <!-- Manage Stations -->
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <a href="{{ route('trains.search') }}" class="btn btn-outline-secondary btn-block py-3 font-weight-bold shadow-sm h-100">
                            <i class="fas fa-map-marker-alt fa-2x d-block mb-2 text-secondary"></i>
                            Manage Stations
                        </a>
                    </div>

                    <!-- Manage Schedules -->
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-primary btn-block py-3 font-weight-bold shadow-sm h-100">
                            <i class="fas fa-calendar-plus fa-2x d-block mb-2 text-primary"></i>
                            Manage Schedules
                        </a>
                    </div>

                    <!-- Manage Seats -->
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <button type="button" class="btn btn-outline-success btn-block py-3 font-weight-bold shadow-sm h-100" data-toggle="modal" data-target="#manageSeatsModal">
                            <i class="fas fa-couch fa-2x d-block mb-2 text-success"></i>
                            Manage Seats
                        </button>
                    </div>

                    <!-- View Bookings -->
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-purple btn-block py-3 font-weight-bold shadow-sm h-100" style="color: #6f42c1; border-color: #6f42c1;">
                            <i class="fas fa-ticket-alt fa-2x d-block mb-2" style="color: #6f42c1;"></i>
                            View Bookings
                        </a>
                    </div>

                    <!-- Manage Users -->
                    <div class="col-lg-2 col-md-4 col-6 mb-2">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-dark btn-block py-3 font-weight-bold shadow-sm h-100">
                            <i class="fas fa-user-shield fa-2x d-block mb-2 text-dark"></i>
                            Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings Table Card -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-clipboard-list text-primary mr-2"></i> Recent Passenger Bookings
                </h3>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold">
                    View All ({{ $stats['total_bookings'] ?? 0 }}) <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0 text-nowrap">
                    <thead class="thead-dark">
                        <tr>
                            <th>PNR Code</th>
                            <th>Booked By</th>
                            <th>Passenger</th>
                            <th>Train & Route</th>
                            <th>Journey Date & Time</th>
                            <th>Seat Allocation</th>
                            <th>Fare</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-primary font-monospace">{{ $booking->booking_code }}</span>
                                    <small class="d-block text-muted">ID #{{ $booking->id }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $booking->user->name }}</div>
                                    <small class="text-muted">{{ $booking->user->email }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">
                                        {{ $booking->passenger ? $booking->passenger->name : 'N/A' }}
                                    </div>
                                    <small class="text-muted">{{ $booking->passenger ? $booking->passenger->phone : '' }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $booking->trainSchedule->train->train_name }}</div>
                                    <small class="text-muted">
                                        {{ $booking->trainSchedule->departureStation->name }} &rarr; {{ $booking->trainSchedule->arrivalStation->name }}
                                    </small>
                                </td>
                                <td>
                                    <div><strong>{{ $booking->trainSchedule->formatted_journey_date }}</strong></div>
                                    <small class="text-muted">Dep: {{ $booking->trainSchedule->formatted_departure_time }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info px-2 py-1 font-weight-bold">
                                        Coach {{ $booking->seat->coach }} - Seat {{ $booking->seat->seat_number }}
                                    </span>
                                    <small class="d-block text-muted">{{ $booking->seat->seat_class }}</small>
                                </td>
                                <td>
                                    <strong class="text-success">{{ $booking->formatted_fare }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $booking->status_badge_class }} text-uppercase px-2 py-1">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('bookings.confirmation', $booking) }}" class="btn btn-info btn-xs font-weight-bold" title="View E-Ticket Slip">
                                        <i class="fas fa-eye mr-1"></i> View Slip
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-ticket-alt fa-3x mb-3 text-secondary"></i>
                                    <h5 class="font-weight-bold text-dark">No Recent Bookings</h5>
                                    <p class="text-muted mb-0">No tickets have been reserved yet in the system.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentBookings->isNotEmpty())
                <div class="card-footer bg-white text-center py-2">
                    <a href="{{ route('admin.bookings.index') }}" class="text-primary font-weight-bold small">
                        View complete bookings history and filters &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Select Train for Seat Management -->
<div class="modal fade" id="manageSeatsModal" tabindex="-1" role="dialog" aria-labelledby="manageSeatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="manageSeatsModalLabel">
                    <i class="fas fa-couch mr-2"></i> Select Train to Manage Seats
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3">Choose a train from the active fleet to configure coaches and individual seat layouts:</p>
                <div class="list-group">
                    @forelse($trains as $train)
                        <a href="{{ route('admin.trains.seats.index', $train) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-0">
                                    <i class="fas fa-subway text-info mr-2"></i> {{ $train->train_name }}
                                </h6>
                                <small class="text-muted">Train Number: {{ $train->train_number }}</small>
                            </div>
                            <span class="badge badge-primary badge-pill">
                                {{ $train->total_seats }} Seats
                            </span>
                        </a>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <p>No trains registered yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
