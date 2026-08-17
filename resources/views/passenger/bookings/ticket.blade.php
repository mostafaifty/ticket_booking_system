@extends('layouts.master')

@section('title', 'E-Ticket Slip - ' . $booking->booking_code)
@section('page_title', 'Passenger E-Ticket Slip')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('passenger.bookings.index') }}">My Bookings</a></li>
    <li class="breadcrumb-item active">Ticket #{{ $booking->id }}</li>
@endsection

@section('content')
<!-- Top Actions Bar (Hidden on Print) -->
<div class="row mb-3 no-print">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <a href="{{ route('passenger.bookings.index') }}" class="btn btn-outline-secondary font-weight-bold">
            <i class="fas fa-arrow-left mr-1"></i> Back to Booking History
        </a>
        <div>
            <button type="button" class="btn btn-primary font-weight-bold shadow-sm" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Print Ticket
            </button>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10 col-12">
        <!-- Official Printable Ticket Card -->
        <div class="card card-outline card-primary shadow-lg ticket-container" id="printable-ticket">
            <!-- Ticket Header -->
            <div class="card-header bg-white border-bottom p-4">
                <div class="row align-items-center">
                    <div class="col-sm-7 text-center text-sm-left mb-3 mb-sm-0">
                        <div class="d-flex align-items-center justify-content-center justify-content-sm-start">
                            <i class="fas fa-train fa-3x text-primary mr-3"></i>
                            <div>
                                <h3 class="font-weight-bold text-dark mb-0">BANGLADESH RAILWAY</h3>
                                <div class="text-muted text-uppercase small font-weight-bold letter-spacing-1">
                                    Official Electronic Passenger Ticket (E-Ticket)
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-right">
                        <div class="pnr-box">
                            <small class="text-muted text-uppercase d-block font-weight-bold">Booking ID / Code</small>
                            <h3 class="font-weight-bold text-primary mb-0 font-monospace">{{ $booking->booking_code }}</h3>
                            <div class="mt-1">
                                <span class="badge {{ $booking->status_badge_class }} text-uppercase px-3 py-1 font-weight-bold">
                                    <i class="fas fa-check-circle mr-1"></i> {{ $booking->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Body -->
            <div class="card-body p-4">
                <!-- Train & Route Schedule Information -->
                <div class="bg-light p-3 rounded border mb-4">
                    <div class="row align-items-center text-center text-md-left">
                        <!-- Train Info -->
                        <div class="col-md-3 border-right mb-3 mb-md-0">
                            <small class="text-muted text-uppercase font-weight-bold d-block">Train Name</small>
                            <h4 class="font-weight-bold text-dark mb-1">{{ $booking->trainSchedule->train->train_name }}</h4>
                            <small class="text-muted font-weight-bold d-block">Train Number:</small>
                            <span class="badge badge-dark text-md px-2 py-1">{{ $booking->trainSchedule->train->train_number }}</span>
                            <span class="badge badge-info">{{ $booking->trainSchedule->train->train_type }}</span>
                        </div>

                        <!-- Route & Timings -->
                        <div class="col-md-6 border-right mb-3 mb-md-0">
                            <div class="d-flex justify-content-around align-items-center">
                                <div>
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Departure Station</small>
                                    <strong class="text-dark h5 mb-0 d-block">{{ $booking->trainSchedule->departureStation->name }}</strong>
                                    <div class="text-primary font-weight-bold h6 mb-0">{{ $booking->trainSchedule->formatted_departure_time }}</div>
                                    <small class="text-muted">Code: {{ $booking->trainSchedule->departureStation->code }}</small>
                                </div>
                                <div class="px-3 text-center">
                                    <i class="fas fa-arrow-right fa-2x text-primary d-block"></i>
                                    <small class="font-weight-bold text-dark d-block mt-1">{{ $booking->trainSchedule->formatted_journey_date }}</small>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase font-weight-bold d-block">Arrival Station</small>
                                    <strong class="text-dark h5 mb-0 d-block">{{ $booking->trainSchedule->arrivalStation->name }}</strong>
                                    <div class="text-primary font-weight-bold h6 mb-0">{{ $booking->trainSchedule->formatted_arrival_time }}</div>
                                    <small class="text-muted">Code: {{ $booking->trainSchedule->arrivalStation->code }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Seat Allocation -->
                        <div class="col-md-3 text-center">
                            <small class="text-muted text-uppercase font-weight-bold d-block">Coach & Seat Number</small>
                            <h2 class="font-weight-bold text-success mb-1">
                                {{ $booking->seat->coach }}-{{ $booking->seat->seat_number }}
                            </h2>
                            <span class="badge badge-primary px-2 py-1 font-weight-bold">
                                {{ $booking->seat->seat_class }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Passenger Details Table -->
                <h5 class="font-weight-bold text-dark mb-3">
                    <i class="fas fa-user-check text-primary mr-1"></i> Passenger Information
                </h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Passenger Name</th>
                                <th>Phone Number</th>
                                <th>Gender / Age</th>
                                <th>Coach</th>
                                <th>Seat Number</th>
                                <th>Class</th>
                                <th>Fare</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-weight-bold text-muted">#{{ $booking->id }}</td>
                                <td class="font-weight-bold text-dark">
                                    {{ $booking->passenger ? $booking->passenger->name : ($booking->user ? $booking->user->name : 'N/A') }}
                                </td>
                                <td>
                                    {{ $booking->passenger ? $booking->passenger->phone : ($booking->user ? $booking->user->phone : 'N/A') }}
                                </td>
                                <td>
                                    {{ ucfirst($booking->passenger->gender ?? 'N/A') }}
                                    @if($booking->passenger && $booking->passenger->age)
                                        ({{ $booking->passenger->age }} yrs)
                                    @endif
                                </td>
                                <td class="font-weight-bold text-primary">{{ $booking->seat->coach }}</td>
                                <td class="font-weight-bold text-success">{{ $booking->seat->seat_number }}</td>
                                <td><span class="badge badge-info">{{ $booking->seat->seat_class }}</span></td>
                                <td class="font-weight-bold text-success">{{ $booking->formatted_fare }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Fare Breakdown & Operational Notes -->
                <div class="row">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="font-weight-bold text-dark mb-2">
                                <i class="fas fa-info-circle text-info mr-1"></i> Travel Guidelines:
                            </h6>
                            <ul class="small text-muted pl-3 mb-0">
                                <li><strong>Journey Date:</strong> {{ $booking->trainSchedule->formatted_journey_date }}</li>
                                <li><strong>Departure Time:</strong> {{ $booking->trainSchedule->formatted_departure_time }} (Please arrive at station 20 minutes prior).</li>
                                <li>Carry a valid photo identification matching the passenger details.</li>
                                <li>This electronic passenger ticket is valid for single journey travel.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="p-3 bg-light rounded border h-100">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Ticket Fare:</span>
                                <span class="font-weight-bold text-dark">{{ $booking->formatted_fare }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Booking Date & Time:</span>
                                <span class="small font-weight-bold text-dark">{{ $booking->formatted_booking_date }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Booking Status:</span>
                                <span class="badge {{ $booking->status_badge_class }} text-uppercase">{{ $booking->status }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-dark h5 mb-0">Grand Total:</strong>
                                <strong class="text-success h4 mb-0">{{ $booking->formatted_fare }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Footer (Hidden on Print) -->
            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center no-print">
                <a href="{{ route('passenger.bookings.index') }}" class="btn btn-secondary font-weight-bold">
                    <i class="fas fa-list mr-1"></i> Booking History
                </a>
                <div class="d-flex align-items-center gap-2">
                    @if($booking->isCancellable())
                        <button type="button" class="btn btn-danger font-weight-bold mr-2" data-toggle="modal" data-target="#cancelTicketModal">
                            <i class="fas fa-ban mr-1"></i> Cancel Ticket
                        </button>
                    @endif
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@if($booking->isCancellable())
    <!-- Cancellation Confirmation Modal -->
    <div class="modal fade no-print" id="cancelTicketModal" tabindex="-1" role="dialog" aria-labelledby="cancelTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="cancelTicketModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Confirm Ticket Cancellation
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-ticket-alt fa-3x text-danger mb-3"></i>
                    <h5 class="font-weight-bold text-dark mb-2">Cancel Reservation: {{ $booking->booking_code }}?</h5>
                    <p class="text-muted mb-3">
                        Are you sure you want to cancel your ticket for <strong>{{ $booking->trainSchedule->train->train_name }}</strong>?
                    </p>
                    <div class="alert alert-warning text-left small mb-0">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Important Note:</strong> Your reserved seat (<strong>Coach {{ $booking->seat->coach }}, Seat {{ $booking->seat->seat_number }}</strong>) will be released immediately and made available for other passengers.
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Keep Ticket
                    </button>
                    <form method="POST" action="{{ route('passenger.bookings.cancel', $booking) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger font-weight-bold">
                            <i class="fas fa-trash-alt mr-1"></i> Yes, Cancel Ticket
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
@media print {
    /* Hide layout navigation, headers, footers during printing */
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
    .ticket-container {
        border: 2px solid #222 !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
}
</style>
@endsection
