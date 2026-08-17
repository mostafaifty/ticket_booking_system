@extends('layouts.master')

@section('title', 'Select Seat - ' . $schedule->train->train_name)
@section('page_title', 'Seat Selection: ' . $schedule->train->train_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('trains.search') }}">Train Search</a></li>
    <li class="breadcrumb-item active">Select Seats</li>
@endsection

@section('content')
<!-- Trip Overview Banner -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3 border-right text-center text-md-left mb-3 mb-md-0">
                        <h4 class="font-weight-bold text-dark mb-1">
                            <i class="fas fa-subway text-primary mr-1"></i> {{ $schedule->train->train_name }}
                        </h4>
                        <span class="badge badge-info">{{ $schedule->train->train_number }}</span>
                        <span class="badge badge-secondary">{{ $schedule->train->train_type }}</span>
                    </div>

                    <div class="col-md-5 border-right text-center mb-3 mb-md-0">
                        <div class="d-flex justify-content-around align-items-center">
                            <div>
                                <small class="text-muted text-uppercase d-block">From</small>
                                <strong class="text-dark">{{ $schedule->departureStation->name }}</strong>
                                <div class="text-info font-weight-bold">{{ $schedule->formatted_departure_time }}</div>
                            </div>
                            <div class="px-3 text-muted">
                                <i class="fas fa-long-arrow-alt-right fa-2x text-secondary"></i>
                                <div class="small font-weight-bold text-dark">{{ $schedule->formatted_journey_date }}</div>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase d-block">To</small>
                                <strong class="text-dark">{{ $schedule->arrivalStation->name }}</strong>
                                <div class="text-info font-weight-bold">{{ $schedule->formatted_arrival_time }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-center">
                        <div class="d-flex justify-content-around align-items-center">
                            <div>
                                <small class="text-muted text-uppercase d-block">Base Fare</small>
                                <span class="h4 font-weight-bold text-success">৳ {{ number_format($schedule->fare, 2) }}</span>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase d-block">Availability</small>
                                <span class="badge badge-success px-2 py-1" style="font-size: 0.9rem;">
                                    {{ $schedule->available_seats_count }} Seats Left
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seat Legend -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card bg-light shadow-none border py-2 px-3">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                <div class="d-flex align-items-center mr-4">
                    <span class="seat-sample available-sample mr-2"></span>
                    <strong class="text-dark small">Available Seat (Click to Select)</strong>
                </div>
                <div class="d-flex align-items-center mr-4">
                    <span class="seat-sample selected-sample mr-2"></span>
                    <strong class="text-primary small">Selected Seat</strong>
                </div>
                <div class="d-flex align-items-center">
                    <span class="seat-sample booked-sample mr-2"></span>
                    <strong class="text-danger small">Booked / Occupied (Not Available)</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Seat Layout Area (Left Side) -->
    <div class="col-lg-8">
        @forelse($coaches as $coachName => $seats)
            <div class="card card-outline card-info shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-couch text-info mr-2"></i> Coach {{ $coachName }}
                    </h5>
                    <div>
                        <span class="badge badge-primary">{{ $seats->first()->seat_class }}</span>
                        <span class="badge badge-secondary">{{ $seats->count() }} Seats in Coach</span>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <!-- Coach Layout Container -->
                    <div class="train-coach-container p-3 bg-white rounded border">
                        <div class="d-flex justify-content-between text-muted small border-bottom pb-2 mb-3">
                            <span><i class="fas fa-door-open mr-1"></i> Front Entrance</span>
                            <span>Coach: <strong>{{ $coachName }}</strong></span>
                            <span>Rear Exit <i class="fas fa-door-open ml-1"></i></span>
                        </div>

                        <!-- Seat Grid -->
                        <div class="d-flex flex-wrap gap-2 justify-content-start">
                            @foreach($seats as $seat)
                                @php
                                    $isBooked = in_array($seat->id, $bookedSeatIds);
                                @endphp

                                <div class="seat-wrapper m-1 text-center">
                                    @if($isBooked)
                                        <button type="button" 
                                                class="btn btn-seat btn-seat-booked" 
                                                disabled 
                                                title="Seat {{ $seat->label }} is already booked">
                                            <i class="fas fa-lock small d-block mb-1"></i>
                                            <span class="seat-num">{{ $seat->seat_number }}</span>
                                            <small class="d-block text-white-50" style="font-size: 0.65rem;">BOOKED</small>
                                        </button>
                                    @else
                                        <button type="button" 
                                                class="btn btn-seat btn-seat-available" 
                                                id="seat-btn-{{ $seat->id }}"
                                                data-seat-id="{{ $seat->id }}"
                                                data-seat-label="{{ $seat->label }}"
                                                data-coach="{{ $seat->coach }}"
                                                data-seat-number="{{ $seat->seat_number }}"
                                                data-seat-class="{{ $seat->seat_class }}"
                                                onclick="selectSeat(this)"
                                                title="Select Seat {{ $seat->label }}">
                                            <i class="fas fa-chair small d-block mb-1"></i>
                                            <span class="seat-num font-weight-bold">{{ $seat->seat_number }}</span>
                                            <small class="d-block text-muted" style="font-size: 0.65rem;">{{ $seat->coach }}</small>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-chair fa-3x text-muted mb-3"></i>
                    <h5 class="font-weight-bold text-dark">No Seats Configured for this Train</h5>
                    <p class="text-muted">Please contact railway system administration.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Booking Confirmation Sidebar (Right Side) -->
    <div class="col-lg-4">
        <div class="card card-success card-outline shadow-sm sticky-top" style="top: 80px; z-index: 10;">
            <div class="card-header bg-white">
                <h4 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-ticket-alt text-success mr-2"></i> Reservation Details
                </h4>
            </div>
            
            <form method="POST" action="{{ route('bookings.store', $schedule) }}" id="seat-booking-form">
                @csrf
                <input type="hidden" name="seat_id" id="selected_seat_id" value="{{ old('seat_id') }}" required>

                <div class="card-body">
                    <!-- Selected Seat Preview Box -->
                    <div class="alert alert-info border-info mb-3 text-center" id="selected-seat-box">
                        <small class="text-uppercase font-weight-bold d-block text-muted">Selected Seat</small>
                        <h4 class="font-weight-bold text-dark mb-0" id="selected-seat-display">
                            <span class="text-danger small font-weight-normal">No seat selected yet.</span>
                        </h4>
                        <div class="text-muted small mt-1" id="selected-coach-display">Click any green seat in the coach map.</div>
                    </div>

                    <!-- Fare Breakdown -->
                    <div class="bg-light p-3 rounded border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Ticket Fare:</span>
                            <span class="font-weight-bold">৳ {{ number_format($schedule->fare, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Service Fee:</span>
                            <span class="text-success font-weight-bold">৳ 0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <strong class="text-dark">Total Amount:</strong>
                            <strong class="text-success h5 mb-0">৳ {{ number_format($schedule->fare, 2) }}</strong>
                        </div>
                    </div>

                    @auth
                        <!-- Passenger Information Form -->
                        <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fas fa-user-edit text-primary mr-1"></i> Passenger Information
                        </h6>

                        <div class="form-group mb-2">
                            <label for="passenger_name" class="text-sm font-weight-bold">Passenger Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="passenger_name" 
                                   id="passenger_name" 
                                   class="form-control form-control-sm @error('passenger_name') is-invalid @enderror" 
                                   value="{{ old('passenger_name', auth()->user()->name) }}" 
                                   required>
                            @error('passenger_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="passenger_phone" class="text-sm font-weight-bold">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="passenger_phone" 
                                   id="passenger_phone" 
                                   class="form-control form-control-sm @error('passenger_phone') is-invalid @enderror" 
                                   value="{{ old('passenger_phone', auth()->user()->phone ?? '+8801') }}" 
                                   required>
                            @error('passenger_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-6 form-group mb-2">
                                <label for="age" class="text-sm font-weight-bold">Age</label>
                                <input type="number" 
                                       name="age" 
                                       id="age" 
                                       class="form-control form-control-sm @error('age') is-invalid @enderror" 
                                       value="{{ old('age', 28) }}" 
                                       min="1" 
                                       max="120">
                            </div>
                            <div class="col-6 form-group mb-2">
                                <label for="gender" class="text-sm font-weight-bold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-control form-control-sm @error('gender') is-invalid @enderror" required>
                                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="nid_or_passport" class="text-sm font-weight-bold">NID / Passport No. (Optional)</label>
                            <input type="text" 
                                   name="nid_or_passport" 
                                   id="nid_or_passport" 
                                   class="form-control form-control-sm @error('nid_or_passport') is-invalid @enderror" 
                                   value="{{ old('nid_or_passport') }}" 
                                   placeholder="e.g. 1995876543210">
                        </div>

                        <button type="submit" class="btn btn-success btn-block font-weight-bold shadow-sm py-2" id="confirm-booking-btn" disabled>
                            <i class="fas fa-check-circle mr-1"></i> Confirm & Book Seat
                        </button>
                    @else
                        <div class="alert alert-warning text-center py-3 mb-0">
                            <i class="fas fa-lock fa-2x text-warning mb-2"></i>
                            <h6 class="font-weight-bold text-dark mb-1">Account Required</h6>
                            <p class="small text-muted mb-3">Please sign in or create an account to book your selected seat.</p>
                            <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-sm font-weight-bold">
                                <i class="fas fa-sign-in-alt mr-1"></i> Sign In to Reserve
                            </a>
                        </div>
                    @endauth
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Seat Styling */
.seat-sample {
    display: inline-block;
    width: 22px;
    height: 22px;
    border-radius: 4px;
    vertical-align: middle;
}
.available-sample {
    background-color: #28a745;
    border: 2px solid #1e7e34;
}
.selected-sample {
    background-color: #007bff;
    border: 2px solid #0056b3;
}
.booked-sample {
    background-color: #dc3545;
    border: 2px solid #bd2130;
}

.btn-seat {
    width: 58px;
    height: 64px;
    padding: 6px 2px;
    border-radius: 6px;
    transition: all 0.2s ease-in-out;
}
.btn-seat-available {
    background-color: #e8f5e9;
    border: 2px solid #28a745;
    color: #1e7e34;
}
.btn-seat-available:hover {
    background-color: #28a745;
    color: #fff;
    transform: scale(1.06);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.btn-seat-selected {
    background-color: #007bff !important;
    border-color: #0056b3 !important;
    color: #ffffff !important;
    transform: scale(1.08);
    box-shadow: 0 4px 10px rgba(0,123,255,0.35);
}
.btn-seat-booked {
    background-color: #dc3545;
    border: 2px solid #bd2130;
    color: #ffffff;
    opacity: 0.75;
    cursor: not-allowed !important;
}
</style>

<script>
function selectSeat(button) {
    const seatId = button.getAttribute('data-seat-id');
    const seatLabel = button.getAttribute('data-seat-label');
    const coach = button.getAttribute('data-coach');
    const seatNum = button.getAttribute('data-seat-number');
    const seatClass = button.getAttribute('data-seat-class');

    // Remove selected class from previously selected seats
    document.querySelectorAll('.btn-seat-available').forEach(btn => {
        btn.classList.remove('btn-seat-selected');
    });

    // Add selected class to current button
    button.classList.add('btn-seat-selected');

    // Set hidden input
    document.getElementById('selected_seat_id').value = seatId;

    // Update Display Box
    document.getElementById('selected-seat-display').innerHTML = '<span class="text-primary font-weight-bold"><i class="fas fa-check mr-1"></i> ' + seatLabel + '</span>';
    document.getElementById('selected-coach-display').innerText = 'Coach: ' + coach + ' | Number: ' + seatNum + ' (' + seatClass + ')';

    // Enable Booking Button
    const confirmBtn = document.getElementById('confirm-booking-btn');
    if (confirmBtn) {
        confirmBtn.disabled = false;
    }
}

// Re-select seat on validation error redirect
document.addEventListener('DOMContentLoaded', function () {
    const previousSeatId = document.getElementById('selected_seat_id').value;
    if (previousSeatId) {
        const btn = document.getElementById('seat-btn-' + previousSeatId);
        if (btn) {
            selectSeat(btn);
        }
    }
});
</script>
@endsection
