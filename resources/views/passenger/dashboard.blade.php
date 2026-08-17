@extends('layouts.master')

@section('title', 'Passenger Dashboard')
@section('page_title', 'Passenger Portal Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('passenger.dashboard') }}">Passenger</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Welcome Banner with Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body py-4 px-4">
                <div class="row align-items-center">
                    <div class="col-md-7 text-center text-md-left mb-3 mb-md-0">
                        <h3 class="font-weight-bold text-dark mb-1">
                            Welcome back, {{ $user->name }}!
                        </h3>
                        <p class="text-muted mb-0">
                            Manage your train ticket reservations, check upcoming journey schedules, and download e-tickets from your dashboard.
                        </p>
                    </div>
                    <div class="col-md-5 text-center text-md-right">
                        <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-2">
                            <a href="{{ route('trains.search') }}" class="btn btn-primary font-weight-bold shadow-sm mr-2 mb-2 mb-sm-0">
                                <i class="fas fa-search mr-1"></i> Search Train
                            </a>
                            <a href="{{ route('passenger.bookings.index') }}" class="btn btn-success font-weight-bold shadow-sm mr-2 mb-2 mb-sm-0">
                                <i class="fas fa-ticket-alt mr-1"></i> My Bookings
                            </a>
                            <a href="{{ route('passenger.profile') }}" class="btn btn-outline-dark font-weight-bold shadow-sm">
                                <i class="fas fa-user-cog mr-1"></i> My Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Metrics Overview (3 Stat Boxes) -->
<div class="row">
    <!-- Total Bookings -->
    <div class="col-md-4 col-12">
        <div class="small-box bg-primary shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_bookings'] ?? 0 }}</h3>
                <p class="font-weight-bold">Total Bookings</p>
            </div>
            <div class="icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <a href="{{ route('passenger.bookings.index', ['tab' => 'all']) }}" class="small-box-footer">
                All Reservation Records <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Bookings -->
    <div class="col-md-4 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ $stats['active_bookings'] ?? 0 }}</h3>
                <p class="font-weight-bold">Active Bookings</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <a href="{{ route('passenger.bookings.index', ['tab' => 'current']) }}" class="small-box-footer">
                Confirmed Upcoming Trips <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Cancelled Bookings -->
    <div class="col-md-4 col-6">
        <div class="small-box bg-danger shadow-sm">
            <div class="inner">
                <h3>{{ $stats['cancelled_bookings'] ?? 0 }}</h3>
                <p class="font-weight-bold">Cancelled Bookings</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <a href="{{ route('passenger.bookings.index', ['tab' => 'past']) }}" class="small-box-footer">
                Cancelled History <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Next Upcoming Journey Card (If Any) -->
<div class="row">
    <div class="col-12">
        @if($upcomingJourney)
            <div class="card card-success card-outline shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title font-weight-bold text-success mb-0">
                        <i class="fas fa-route mr-2"></i> Next Upcoming Journey
                    </h4>
                    <span class="badge badge-success px-3 py-1 font-weight-bold text-uppercase">
                        <i class="fas fa-check-circle mr-1"></i> {{ $upcomingJourney->status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row align-items-center text-center text-md-left">
                        <!-- Train Info -->
                        <div class="col-md-3 border-right mb-3 mb-md-0">
                            <small class="text-muted text-uppercase font-weight-bold d-block">Train Name</small>
                            <h4 class="font-weight-bold text-dark mb-1">{{ $upcomingJourney->trainSchedule->train->train_name }}</h4>
                            <span class="badge badge-info">{{ $upcomingJourney->trainSchedule->train->train_number }}</span>
                            <span class="badge badge-secondary">{{ $upcomingJourney->trainSchedule->train->train_type }}</span>
                        </div>

                        <!-- Route & Timings -->
                        <div class="col-md-5 border-right mb-3 mb-md-0">
                            <div class="d-flex justify-content-around align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase font-weight-bold d-block">From</small>
                                    <strong class="text-dark">{{ $upcomingJourney->trainSchedule->departureStation->name }}</strong>
                                    <div class="text-primary font-weight-bold">{{ $upcomingJourney->trainSchedule->formatted_departure_time }}</div>
                                </div>
                                <div class="px-2 text-center">
                                    <i class="fas fa-long-arrow-alt-right fa-2x text-success"></i>
                                    <div class="small font-weight-bold text-dark">{{ $upcomingJourney->trainSchedule->formatted_journey_date }}</div>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase font-weight-bold d-block">To</small>
                                    <strong class="text-dark">{{ $upcomingJourney->trainSchedule->arrivalStation->name }}</strong>
                                    <div class="text-primary font-weight-bold">{{ $upcomingJourney->trainSchedule->formatted_arrival_time }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Seat Allocation & PNR -->
                        <div class="col-md-2 border-right mb-3 mb-md-0 text-center">
                            <small class="text-muted text-uppercase font-weight-bold d-block">Allocated Seat</small>
                            <h3 class="font-weight-bold text-primary mb-0">
                                {{ $upcomingJourney->seat->coach }}-{{ $upcomingJourney->seat->seat_number }}
                            </h3>
                            <span class="badge badge-info">{{ $upcomingJourney->seat->seat_class }}</span>
                            <small class="d-block text-muted mt-1 font-monospace">PNR: {{ $upcomingJourney->booking_code }}</small>
                        </div>

                        <!-- Action Button -->
                        <div class="col-md-2 text-center">
                            <a href="{{ route('passenger.bookings.ticket', $upcomingJourney) }}" class="btn btn-success btn-block font-weight-bold shadow-sm">
                                <i class="fas fa-print mr-1"></i> View Ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card card-light shadow-sm mb-4 border">
                <div class="card-body py-4 text-center">
                    <i class="fas fa-calendar-times fa-3x text-secondary mb-2"></i>
                    <h5 class="font-weight-bold text-dark mb-1">No Upcoming Journeys Scheduled</h5>
                    <p class="text-muted small mb-3">You do not have any active upcoming train trips right now.</p>
                    <a href="{{ route('trains.search') }}" class="btn btn-primary btn-sm font-weight-bold">
                        <i class="fas fa-search mr-1"></i> Search Trains & Book Seats
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Recent Bookings Table & Profile Summary Row -->
<div class="row">
    <!-- Recent Bookings Table Card (Left Column) -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-ticket-alt text-primary mr-1"></i> Recent Bookings
                </h4>
                <a href="{{ route('passenger.bookings.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold">
                    View All History <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0 text-nowrap">
                    <thead class="thead-light">
                        <tr>
                            <th>PNR Code</th>
                            <th>Train & Route</th>
                            <th>Date & Time</th>
                            <th>Seat</th>
                            <th>Fare</th>
                            <th>Status</th>
                            <th class="text-right">Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-primary font-monospace">{{ $booking->booking_code }}</span>
                                </td>
                                <td>
                                    <strong>{{ $booking->trainSchedule->train->train_name }}</strong>
                                    <small class="d-block text-muted">
                                        {{ $booking->trainSchedule->departureStation->code }} &rarr; {{ $booking->trainSchedule->arrivalStation->code }}
                                    </small>
                                </td>
                                <td>
                                    <div><strong>{{ $booking->trainSchedule->formatted_journey_date }}</strong></div>
                                    <small class="text-muted">{{ $booking->trainSchedule->formatted_departure_time }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $booking->seat->coach }}-{{ $booking->seat->seat_number }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success">{{ $booking->formatted_fare }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $booking->status_badge_class }} text-uppercase">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('passenger.bookings.ticket', $booking) }}" class="btn btn-info btn-xs font-weight-bold">
                                        <i class="fas fa-eye mr-1"></i> View Ticket
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <p class="mb-2">You haven't reserved any train tickets yet.</p>
                                    <a href="{{ route('trains.search') }}" class="btn btn-success btn-sm font-weight-bold">
                                        <i class="fas fa-search mr-1"></i> Book Your First Journey
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentBookings->isNotEmpty())
                <div class="card-footer bg-white text-center py-2">
                    <a href="{{ route('passenger.bookings.index') }}" class="text-primary font-weight-bold small">
                        View complete booking history & tickets &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Profile Summary Card (Right Column) -->
    <div class="col-lg-4 mb-4">
        <div class="card card-primary card-outline shadow-sm h-100">
            <div class="card-header bg-white">
                <h4 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-user-circle text-primary mr-1"></i> My Profile
                </h4>
            </div>
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h4 class="profile-username text-center font-weight-bold mb-1">{{ $user->name }}</h4>
                <p class="text-muted text-center"><span class="badge badge-success px-2 py-1">Verified Passenger</span></p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Email Address</b> <span class="text-muted text-truncate" style="max-width: 170px;">{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Phone Number</b> <span class="text-muted">{{ $user->phone ?? 'Not provided' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Member Since</b> <span class="text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Today' }}</span>
                    </li>
                </ul>

                <a href="{{ route('passenger.profile') }}" class="btn btn-primary btn-block font-weight-bold shadow-sm">
                    <i class="fas fa-user-edit mr-1"></i> Update My Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
