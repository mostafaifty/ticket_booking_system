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
            <a href="{{ route('passenger.bookings.index') }}" class="small-box-footer">
                My Bookings History <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ $stats['upcoming_trips'] ?? 0 }}</h3>
                <p>Confirmed Trips</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <a href="{{ route('passenger.bookings.index', ['status' => 'confirmed']) }}" class="small-box-footer">
                View Confirmed Tickets <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3><i class="fas fa-search"></i></h3>
                <p>Search & Book Trains</p>
            </div>
            <div class="icon">
                <i class="fas fa-subway"></i>
            </div>
            <a href="{{ route('trains.search') }}" class="small-box-footer">
                Find Available Trains <i class="fas fa-arrow-circle-right"></i>
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
            <a href="{{ route('passenger.bookings.index', ['status' => 'cancelled']) }}" class="small-box-footer">
                Cancellations <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Bookings and Profile Summary -->
<div class="row">
    <div class="col-lg-8">
        <!-- Recent Bookings Table Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-ticket-alt text-primary mr-1"></i> Recent Reservations & E-Tickets
                </h3>
                <a href="{{ route('passenger.bookings.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold">
                    View All ({{ $stats['total_bookings'] ?? 0 }})
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
                            <th>Status</th>
                            <th class="text-right">Ticket Slip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td class="font-weight-bold text-primary">{{ $booking->booking_code }}</td>
                                <td>
                                    <strong>{{ $booking->trainSchedule->train->train_name }}</strong>
                                    <small class="d-block text-muted">
                                        {{ $booking->trainSchedule->departureStation->code }} &rarr; {{ $booking->trainSchedule->arrivalStation->code }}
                                    </small>
                                </td>
                                <td>
                                    <div>{{ $booking->trainSchedule->formatted_journey_date }}</div>
                                    <small class="text-muted">{{ $booking->trainSchedule->formatted_departure_time }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $booking->seat->coach }}-{{ $booking->seat->seat_number }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $booking->status_badge_class }} text-uppercase">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('bookings.confirmation', $booking) }}" class="btn btn-info btn-xs font-weight-bold">
                                        <i class="fas fa-print mr-1"></i> View Slip
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <p class="mb-2">You have no railway reservations yet.</p>
                                    <a href="{{ route('trains.search') }}" class="btn btn-success btn-sm font-weight-bold">
                                        <i class="fas fa-search mr-1"></i> Find & Book Train Tickets
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Profile Summary Card -->
    <div class="col-lg-4">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-user-circle mr-1"></i> Profile Summary
                </h3>
            </div>
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h4 class="profile-username text-center font-weight-bold">{{ $user->name }}</h4>
                <p class="text-muted text-center"><span class="badge badge-success">Verified Passenger</span></p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Email</b> <span class="text-muted text-truncate" style="max-width: 170px;">{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Phone</b> <span class="text-muted">{{ $user->phone ?? 'Not provided' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Member Since</b> <span class="text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Today' }}</span>
                    </li>
                </ul>

                <a href="{{ route('passenger.profile') }}" class="btn btn-primary btn-block font-weight-bold">
                    <i class="fas fa-user-cog mr-1"></i> Edit My Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
