@extends('layouts.master')

@section('title', 'My Booking History')
@section('page_title', 'My Ticket Bookings & History')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('passenger.dashboard') }}">Passenger</a></li>
    <li class="breadcrumb-item active">My Bookings</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Navigation Tabs for Current vs Past vs All Bookings -->
        <div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="bookingTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $tab === 'current' ? 'active' : '' }}" 
                           href="{{ route('passenger.bookings.index', ['tab' => 'current']) }}">
                            <i class="fas fa-calendar-check text-success mr-1"></i> Current & Upcoming Journeys
                            <span class="badge badge-success ml-1">{{ $currentCount ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $tab === 'past' ? 'active' : '' }}" 
                           href="{{ route('passenger.bookings.index', ['tab' => 'past']) }}">
                            <i class="fas fa-history text-secondary mr-1"></i> Past Trips History
                            <span class="badge badge-secondary ml-1">{{ $pastCount ?? 0 }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold {{ $tab === 'all' ? 'active' : '' }}" 
                           href="{{ route('passenger.bookings.index', ['tab' => 'all']) }}">
                            <i class="fas fa-list text-primary mr-1"></i> All Bookings
                            <span class="badge badge-primary ml-1">{{ $totalCount ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body bg-light">
                <!-- Search Filter Form -->
                <form method="GET" action="{{ route('passenger.bookings.index') }}" class="row align-items-end">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    
                    <div class="col-md-9 form-group mb-2">
                        <label class="text-sm font-weight-bold">Search by PNR Code, Train Name, or Station</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control" placeholder="Search by PNR code, train name, station..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-md-3 form-group mb-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold mr-2">
                            <i class="fas fa-filter mr-1"></i> Search
                        </button>
                        @if(request()->filled('search'))
                            <a href="{{ route('passenger.bookings.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm" title="Clear Search">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings List Table Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h4 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-ticket-alt text-primary mr-2"></i>
                    @if($tab === 'current')
                        Current & Upcoming Journeys ({{ $bookings->total() }})
                    @elseif($tab === 'past')
                        Past Travel History ({{ $bookings->total() }})
                    @else
                        All Railway Bookings ({{ $bookings->total() }})
                    @endif
                </h4>
                <a href="{{ route('trains.search') }}" class="btn btn-success btn-sm font-weight-bold">
                    <i class="fas fa-plus mr-1"></i> Book New Journey
                </a>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0 text-nowrap">
                    <thead class="thead-dark">
                        <tr>
                            <th>Booking ID / Code</th>
                            <th>Train</th>
                            <th>Route (Origin &rarr; Destination)</th>
                            <th>Journey Date & Time</th>
                            <th>Coach & Seat</th>
                            <th>Fare</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-primary font-monospace">{{ $booking->booking_code }}</div>
                                    <small class="text-muted">ID #{{ $booking->id }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $booking->trainSchedule->train->train_name }}</div>
                                    <span class="badge badge-secondary">{{ $booking->trainSchedule->train->train_number }}</span>
                                    <span class="badge badge-info">{{ $booking->trainSchedule->train->train_type }}</span>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                        <strong>{{ $booking->trainSchedule->departureStation->name }}</strong>
                                    </div>
                                    <div class="text-muted small">
                                        &darr; to <strong>{{ $booking->trainSchedule->arrivalStation->name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="far fa-calendar-alt text-info mr-1"></i>
                                        <strong>{{ $booking->trainSchedule->formatted_journey_date }}</strong>
                                    </div>
                                    <small class="text-muted">
                                        <i class="far fa-clock mr-1"></i> Dep: {{ $booking->trainSchedule->formatted_departure_time }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-primary px-2 py-1 font-weight-bold">
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
                                    <a href="{{ route('passenger.bookings.ticket', $booking) }}" class="btn btn-info btn-sm font-weight-bold mr-1" title="View & Print Ticket Slip">
                                        <i class="fas fa-print mr-1"></i> Ticket
                                    </a>
                                    @if($booking->isCancellable())
                                        <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold" data-toggle="modal" data-target="#cancelModal-{{ $booking->id }}" title="Cancel Booking">
                                            <i class="fas fa-ban mr-1"></i> Cancel
                                        </button>

                                        <!-- Cancel Modal -->
                                        <div class="modal fade text-left" id="cancelModal-{{ $booking->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title font-weight-bold">
                                                            <i class="fas fa-exclamation-triangle mr-2"></i> Cancel Reservation: {{ $booking->booking_code }}
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body p-4 text-center">
                                                        <i class="fas fa-ticket-alt fa-3x text-danger mb-3"></i>
                                                        <h5 class="font-weight-bold text-dark mb-2">Are you sure you want to cancel this ticket?</h5>
                                                        <p class="text-muted mb-2">
                                                            Train: <strong>{{ $booking->trainSchedule->train->train_name }}</strong><br>
                                                            Journey Date: <strong>{{ $booking->trainSchedule->formatted_journey_date }}</strong>
                                                        </p>
                                                        <div class="alert alert-warning text-left small mb-0">
                                                            <i class="fas fa-info-circle mr-1"></i> <strong>Seat Release:</strong> Seat <strong>{{ $booking->seat->coach }}-{{ $booking->seat->seat_number }}</strong> will be released immediately for other passengers.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light justify-content-between">
                                                        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Keep Ticket</button>
                                                        <form method="POST" action="{{ route('passenger.bookings.cancel', $booking) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger font-weight-bold">
                                                                <i class="fas fa-trash-alt mr-1"></i> Confirm Cancellation
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-ticket-alt fa-3x mb-3 text-secondary"></i>
                                    <h5 class="font-weight-bold text-dark">No Bookings Found in this Category</h5>
                                    <p class="text-muted mb-3">
                                        @if($tab === 'current')
                                            You don't have any upcoming train journeys scheduled.
                                        @elseif($tab === 'past')
                                            You don't have any past journey history.
                                        @else
                                            No booking records found.
                                        @endif
                                    </p>
                                    <a href="{{ route('trains.search') }}" class="btn btn-primary font-weight-bold">
                                        <i class="fas fa-search mr-1"></i> Search Trains & Reserve Seats
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
                <div class="card-footer bg-white clearfix">
                    <div class="float-right">
                        {{ $bookings->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
