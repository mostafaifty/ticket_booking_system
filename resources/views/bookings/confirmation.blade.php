@extends('layouts.master')

@section('title', 'E-Ticket Confirmation - ' . $booking->booking_code)
@section('page_title', 'Booking Confirmation Voucher')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('passenger.bookings.index') }}">My Bookings</a></li>
    <li class="breadcrumb-item active">{{ $booking->booking_code }}</li>
@endsection

@section('content')
<!-- Success Alert Banner (Hidden on Print) -->
<div class="row no-print">
    <div class="col-12">
        <div class="alert alert-success alert-dismissible shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check-circle"></i> Reservation Confirmed Successfully!</h5>
            Your seat reservation is confirmed. Please save or print your official e-ticket voucher below.
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 col-12">
        <!-- Main E-Ticket Voucher Card -->
        <div class="card card-outline card-primary shadow-lg ticket-voucher" id="ticket-voucher">
            <!-- Voucher Header -->
            <div class="card-header bg-white border-bottom p-4">
                <div class="row align-items-center">
                    <div class="col-sm-6 text-center text-sm-left mb-3 mb-sm-0">
                        <div class="d-flex align-items-center justify-content-center justify-content-sm-start">
                            <i class="fas fa-train fa-3x text-primary mr-3"></i>
                            <div>
                                <h3 class="font-weight-bold text-dark mb-0">BANGLADESH RAILWAY</h3>
                                <small class="text-muted text-uppercase letter-spacing-1 font-weight-bold">Electronic Reservation Slip (E-Ticket)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 text-center text-sm-right">
                        <div class="pnr-badge-container">
                            <span class="text-muted small text-uppercase d-block font-weight-bold">Booking Reference / PNR</span>
                            <h2 class="font-weight-bold text-primary mb-0 letter-spacing-1">{{ $booking->booking_code }}</h2>
                            <span class="badge badge-success px-3 py-1 text-uppercase font-weight-bold mt-1">
                                <i class="fas fa-check-circle mr-1"></i> {{ $booking->status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Voucher Body -->
            <div class="card-body p-4">
                <!-- Journey Route & Timetable Section -->
                <div class="route-overview bg-light p-3 rounded border mb-4">
                    <div class="row align-items-center text-center text-md-left">
                        <div class="col-md-3 border-right mb-3 mb-md-0">
                            <small class="text-muted text-uppercase font-weight-bold d-block">Train Name & Number</small>
                            <h4 class="font-weight-bold text-dark mb-0">{{ $booking->trainSchedule->train->train_name }}</h4>
                            <span class="badge badge-dark">{{ $booking->trainSchedule->train->train_number }}</span>
                            <span class="badge badge-info">{{ $booking->trainSchedule->train->train_type }}</span>
                        </div>

                        <div class="col-md-6 border-right mb-3 mb-md-0">
                            <div class="d-flex justify-content-around align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Departure Station</small>
                                    <strong class="text-dark h5 mb-0">{{ $booking->trainSchedule->departureStation->name }}</strong>
                                    <div class="text-primary font-weight-bold">{{ $booking->trainSchedule->formatted_departure_time }}</div>
                                    <small class="text-muted">({{ $booking->trainSchedule->departureStation->code }})</small>
                                </div>
                                <div class="px-2 text-muted">
                                    <i class="fas fa-long-arrow-alt-right fa-2x text-primary"></i>
                                    <div class="small font-weight-bold text-dark">{{ $booking->trainSchedule->formatted_journey_date }}</div>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Arrival Station</small>
                                    <strong class="text-dark h5 mb-0">{{ $booking->trainSchedule->arrivalStation->name }}</strong>
                                    <div class="text-primary font-weight-bold">{{ $booking->trainSchedule->formatted_arrival_time }}</div>
                                    <small class="text-muted">({{ $booking->trainSchedule->arrivalStation->code }})</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 text-center">
                            <small class="text-muted text-uppercase font-weight-bold d-block">Allocated Seat</small>
                            <h3 class="font-weight-bold text-success mb-0">
                                {{ $booking->seat->coach }}-{{ $booking->seat->seat_number }}
                            </h3>
                            <span class="badge badge-primary px-2 py-1">{{ $booking->seat->seat_class }}</span>
                        </div>
                    </div>
                </div>

                <!-- Passenger Details Table -->
                <h5 class="font-weight-bold text-dark mb-3">
                    <i class="fas fa-user-check text-primary mr-1"></i> Passenger Details
                </h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Passenger Name</th>
                                <th>Contact Phone</th>
                                <th>Gender / Age</th>
                                <th>NID / Passport</th>
                                <th>Coach</th>
                                <th>Seat No</th>
                                <th>Class</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-weight-bold">1</td>
                                <td class="font-weight-bold text-dark">
                                    {{ $booking->passenger ? $booking->passenger->name : ($booking->user ? $booking->user->name : 'N/A') }}
                                </td>
                                <td>{{ $booking->passenger ? $booking->passenger->phone : ($booking->user ? $booking->user->phone : 'N/A') }}</td>
                                <td>
                                    {{ ucfirst($booking->passenger->gender ?? 'N/A') }}
                                    @if($booking->passenger && $booking->passenger->age)
                                        ({{ $booking->passenger->age }} yrs)
                                    @endif
                                </td>
                                <td>{{ $booking->passenger->nid_or_passport ?? 'Not Provided' }}</td>
                                <td class="font-weight-bold text-primary">{{ $booking->seat->coach }}</td>
                                <td class="font-weight-bold text-success">{{ $booking->seat->seat_number }}</td>
                                <td><span class="badge badge-info">{{ $booking->seat->seat_class }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Fare Summary & Booking Meta -->
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-info-circle text-info mr-1"></i> Important Instructions:</h6>
                            <ul class="small text-muted pl-3 mb-0">
                                <li>Please arrive at the station at least 20 minutes prior to train departure.</li>
                                <li>Carry a valid photo ID (NID, Passport, or Student ID) matching this ticket slip.</li>
                                <li>This electronic reservation is valid without a physical counter printout.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Ticket Base Fare:</span>
                                <span class="font-weight-bold">৳ {{ number_format($booking->total_fare, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Reservation Fee:</span>
                                <span class="text-success font-weight-bold">৳ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Booking Date & Time:</span>
                                <span class="small font-weight-bold text-dark">{{ $booking->formatted_booking_date }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-dark h5 mb-0">Total Amount Paid:</strong>
                                <strong class="text-success h4 mb-0">৳ {{ number_format($booking->total_fare, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Voucher Footer & Action Buttons (Hidden on Print) -->
            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center no-print">
                <a href="{{ route('passenger.bookings.index') }}" class="btn btn-secondary font-weight-bold">
                    <i class="fas fa-arrow-left mr-1"></i> My Bookings List
                </a>
                <div>
                    <a href="{{ route('trains.search') }}" class="btn btn-outline-primary font-weight-bold mr-2">
                        <i class="fas fa-search mr-1"></i> Book Another Train
                    </a>
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print E-Ticket Voucher
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide layout chrome during printing */
    .no-print,
    .main-header,
    .main-sidebar,
    .main-footer,
    .content-header,
    .breadcrumb {
        display: none !important;
    }
    .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }
    .ticket-voucher {
        border: 2px solid #333 !important;
        box-shadow: none !important;
    }
}
</style>
@endsection
