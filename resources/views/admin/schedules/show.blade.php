@extends('layouts.master')

@section('title', 'Schedule #' . $schedule->id . ' Details')
@section('page_title', 'Schedule #' . $schedule->id . ' - ' . $schedule->train->train_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
    <li class="breadcrumb-item active">#{{ $schedule->id }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Schedule Main Overview Card -->
    <div class="col-lg-8">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-calendar-check text-primary mr-1"></i> Trip Information
                </h3>
                <div>
                    <span class="badge {{ $schedule->status_badge_class }} text-uppercase px-2 py-1">
                        {{ $schedule->status }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6 border-right">
                        <h6 class="text-muted text-uppercase font-weight-bold mb-2">Origin Station</h6>
                        <h4 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ $schedule->departureStation->name }}
                        </h4>
                        <p class="text-muted mb-2">Code: <span class="badge badge-secondary">{{ $schedule->departureStation->code }}</span> | {{ $schedule->departureStation->location }}</p>
                        <div class="text-info font-weight-bold">
                            <i class="far fa-clock mr-1"></i> Departure: {{ $schedule->formatted_departure_time }}
                        </div>
                    </div>
                    <div class="col-md-6 pl-md-4">
                        <h6 class="text-muted text-uppercase font-weight-bold mb-2">Destination Station</h6>
                        <h4 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-map-marker-alt text-success mr-1"></i> {{ $schedule->arrivalStation->name }}
                        </h4>
                        <p class="text-muted mb-2">Code: <span class="badge badge-secondary">{{ $schedule->arrivalStation->code }}</span> | {{ $schedule->arrivalStation->location }}</p>
                        <div class="text-info font-weight-bold">
                            <i class="far fa-clock mr-1"></i> Arrival: {{ $schedule->formatted_arrival_time }}
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row text-center mt-3">
                    <div class="col-sm-4 mb-2">
                        <div class="text-muted small">JOURNEY DATE</div>
                        <div class="font-weight-bold text-dark h5">{{ $schedule->formatted_journey_date }}</div>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <div class="text-muted small">TICKET FARE</div>
                        <div class="font-weight-bold text-success h5">৳ {{ number_format($schedule->fare, 2) }}</div>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <div class="text-muted small">AVAILABLE SEATS</div>
                        <div class="font-weight-bold {{ $schedule->available_seats_count > 0 ? 'text-primary' : 'text-danger' }} h5">
                            {{ $schedule->available_seats_count }} / {{ $schedule->train->total_seats }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between">
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary font-weight-bold">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Schedules
                </a>
                <div>
                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary font-weight-bold mr-1">
                        <i class="fas fa-edit mr-1"></i> Edit Schedule
                    </a>
                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger font-weight-bold">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bookings on this Schedule -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h3 class="card-title font-weight-bold text-dark">
                    <i class="fas fa-ticket-alt text-info mr-1"></i> Confirmed Bookings ({{ $schedule->bookings->count() }})
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0 text-nowrap">
                    <thead class="thead-light">
                        <tr>
                            <th>PNR / Code</th>
                            <th>Passenger Name</th>
                            <th>Seat</th>
                            <th>Fare Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedule->bookings as $booking)
                            <tr>
                                <td class="font-weight-bold">{{ $booking->booking_code }}</td>
                                <td>
                                    <div>{{ $booking->passenger ? $booking->passenger->name : ($booking->user ? $booking->user->name : 'N/A') }}</div>
                                    <small class="text-muted">{{ $booking->passenger ? $booking->passenger->phone : '' }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $booking->seat ? $booking->seat->label : 'Seat #' . $booking->seat_id }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-success">৳ {{ number_format($booking->total_fare, 2) }}</td>
                                <td>
                                    <span class="badge badge-success text-uppercase">{{ $booking->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No passenger bookings placed on this schedule yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Assigned Train Details -->
    <div class="col-lg-4">
        <div class="card card-info card-outline shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-train text-info mr-1"></i> Assigned Train
                </h3>
            </div>
            <div class="card-body">
                <h4 class="font-weight-bold text-dark">{{ $schedule->train->train_name }}</h4>
                <p class="text-muted mb-3">
                    Train No: <span class="badge badge-dark">{{ $schedule->train->train_number }}</span> | Type: <strong>{{ $schedule->train->train_type }}</strong>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Total Seat Capacity</b> <span>{{ $schedule->train->total_seats }} Seats</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Reserved Seats</b> <span>{{ $schedule->booked_seats_count }} Seats</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Available Seats</b> <span class="text-success font-weight-bold">{{ $schedule->available_seats_count }} Seats</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <b>Train Status</b> <span class="badge badge-success text-uppercase">{{ $schedule->train->status }}</span>
                    </li>
                </ul>

                <!-- Occupancy Progress Bar -->
                @php
                    $total = $schedule->train->total_seats ?: 1;
                    $percent = round(($schedule->booked_seats_count / $total) * 100);
                @endphp
                <div class="mt-3">
                    <div class="d-flex justify-content-between small font-weight-bold mb-1">
                        <span>Occupancy Rate</span>
                        <span>{{ $percent }}%</span>
                    </div>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
