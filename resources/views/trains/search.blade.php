@extends('layouts.master')

@section('title', 'Search Trains & Schedules')
@section('page_title', 'Find & Search Trains')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">Search Trains</li>
@endsection

@section('content')
<!-- Search Box Card -->
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold text-dark">
                    <i class="fas fa-search-location text-primary mr-2"></i> Search Available Train Routes
                </h3>
            </div>
            <div class="card-body bg-light">
                <form method="GET" action="{{ route('trains.search') }}" id="train-search-form">
                    <div class="row align-items-end">
                        <!-- Origin Station -->
                        <div class="col-lg-4 col-md-6 form-group mb-3">
                            <label for="departure_station_id" class="font-weight-bold">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i> From (Departure Station)
                            </label>
                            <select name="departure_station_id" 
                                    id="departure_station_id" 
                                    class="form-control @error('departure_station_id') is-invalid @enderror">
                                <option value="">-- All Departure Stations --</option>
                                @foreach($stations as $station)
                                    <option value="{{ $station->id }}" {{ $selectedDeparture == $station->id ? 'selected' : '' }}>
                                        {{ $station->name }} ({{ $station->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('departure_station_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Destination Station -->
                        <div class="col-lg-4 col-md-6 form-group mb-3">
                            <label for="arrival_station_id" class="font-weight-bold">
                                <i class="fas fa-map-marker-alt text-success mr-1"></i> To (Arrival Station)
                            </label>
                            <select name="arrival_station_id" 
                                    id="arrival_station_id" 
                                    class="form-control @error('arrival_station_id') is-invalid @enderror">
                                <option value="">-- All Arrival Stations --</option>
                                @foreach($stations as $station)
                                    <option value="{{ $station->id }}" {{ $selectedArrival == $station->id ? 'selected' : '' }}>
                                        {{ $station->name }} ({{ $station->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('arrival_station_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Date of Journey -->
                        <div class="col-lg-3 col-md-8 form-group mb-3">
                            <label for="journey_date" class="font-weight-bold">
                                <i class="far fa-calendar-alt text-info mr-1"></i> Date of Journey
                            </label>
                            <input type="date" 
                                   name="journey_date" 
                                   id="journey_date" 
                                   value="{{ $selectedDate ?? '' }}" 
                                   min="{{ date('Y-m-d') }}" 
                                   class="form-control @error('journey_date') is-invalid @enderror">
                            @error('journey_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Search Button -->
                        <div class="col-lg-1 col-md-4 form-group mb-3">
                            <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm" style="height: 38px;">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Search Results Header -->
<div class="row">
    <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-train text-success mr-2"></i> Available Train Schedules
            </h4>
            <small class="text-muted">
                Showing {{ $schedules->count() }} of {{ $schedules->total() }} matching result(s)
                @if($selectedDate)
                    for <strong>{{ date('d M, Y', strtotime($selectedDate)) }}</strong>
                @endif
            </small>
        </div>
        @if($hasSearch)
            <a href="{{ route('trains.search') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times-circle mr-1"></i> Clear Filters
            </a>
        @endif
    </div>
</div>

<!-- Search Results List -->
<div class="row">
    <div class="col-12">
        @forelse($schedules as $schedule)
            <div class="card shadow-sm mb-3 border-left-primary" style="border-left: 5px solid #007bff;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Train Info -->
                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0 border-right">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-3 rounded mr-3 text-primary">
                                    <i class="fas fa-subway fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">{{ $schedule->train->train_name }}</h5>
                                    <div class="text-muted small">
                                        Train No: <strong class="text-dark">{{ $schedule->train->train_number }}</strong>
                                    </div>
                                    <span class="badge badge-info">{{ $schedule->train->train_type }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Route & Timings -->
                        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0 border-right">
                            <div class="d-flex justify-content-between align-items-center text-center">
                                <div>
                                    <span class="text-muted small d-block">DEPARTURE</span>
                                    <strong class="text-dark h6 mb-0">{{ $schedule->formatted_departure_time }}</strong>
                                    <div class="font-weight-bold text-primary small">{{ $schedule->departureStation->name }}</div>
                                </div>
                                
                                <div class="px-2">
                                    <small class="text-muted d-block">
                                        <i class="far fa-calendar-alt"></i> {{ $schedule->formatted_journey_date }}
                                    </small>
                                    <div class="text-muted">
                                        &bull;&mdash;&mdash;&mdash;&gt;&bull;
                                    </div>
                                    <span class="badge {{ $schedule->status_badge_class }}">{{ ucfirst($schedule->status) }}</span>
                                </div>

                                <div>
                                    <span class="text-muted small d-block">ARRIVAL</span>
                                    <strong class="text-dark h6 mb-0">{{ $schedule->formatted_arrival_time }}</strong>
                                    <div class="font-weight-bold text-primary small">{{ $schedule->arrivalStation->name }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Seat Availability & Fare -->
                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0 text-center border-right">
                            <div class="mb-2">
                                <span class="text-muted small d-block">AVAILABLE SEATS</span>
                                @if($schedule->available_seats_count > 0)
                                    <span class="badge badge-success px-3 py-2" style="font-size: 0.95rem;">
                                        <i class="fas fa-chair mr-1"></i> {{ $schedule->available_seats_count }} Seats Available
                                    </span>
                                @else
                                    <span class="badge badge-danger px-3 py-2" style="font-size: 0.95rem;">
                                        <i class="fas fa-times-circle mr-1"></i> Fully Booked
                                    </span>
                                @endif
                            </div>
                            <div>
                                <span class="text-muted small">Base Fare:</span>
                                <span class="h5 font-weight-bold text-success ml-1">৳ {{ number_format($schedule->fare, 2) }}</span>
                            </div>
                        </div>

                        <!-- Action / Booking Status -->
                        <div class="col-lg-2 col-md-6 text-center">
                            @if($schedule->available_seats_count > 0)
                                <a href="{{ route('schedules.seats', $schedule) }}" class="btn btn-primary btn-block font-weight-bold shadow-sm">
                                    <i class="fas fa-ticket-alt mr-1"></i> Select Seats
                                </a>
                                <small class="text-success font-weight-bold d-block mt-1">Ready to Reserve</small>
                            @else
                                <button type="button" class="btn btn-secondary btn-block disabled">
                                    Sold Out
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-train fa-4x text-muted mb-3"></i>
                    <h4 class="font-weight-bold text-dark">No Matching Trains Found</h4>
                    <p class="text-muted mx-auto" style="max-width: 500px;">
                        We couldn't find any scheduled trains for the selected route and date. Please adjust your departure/arrival stations or try a different journey date.
                    </p>
                    <a href="{{ route('trains.search') }}" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-redo mr-1"></i> View All Available Trains
                    </a>
                </div>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($schedules->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $schedules->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection
