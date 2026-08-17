@extends('layouts.master')

@section('title', 'Manage All Bookings')
@section('page_title', 'Passenger Bookings List')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">All Bookings</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter Card -->
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Filter Bookings
                </h3>
            </div>
            <div class="card-body bg-light">
                <form method="GET" action="{{ route('admin.bookings.index') }}" class="row align-items-end">
                    <div class="col-md-4 form-group mb-2">
                        <label class="text-sm font-weight-bold">Search PNR / Code</label>
                        <input type="text" name="pnr" class="form-control form-control-sm" placeholder="e.g. BK-..." value="{{ request('pnr') }}">
                    </div>

                    <div class="col-md-3 form-group mb-2">
                        <label class="text-sm font-weight-bold">Train</label>
                        <select name="train_id" class="form-control form-control-sm">
                            <option value="">-- All Trains --</option>
                            @foreach($trains as $t)
                                <option value="{{ $t->id }}" {{ request('train_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->train_number }} - {{ $t->train_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group mb-2">
                        <label class="text-sm font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">-- All Statuses --</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div class="col-md-2 form-group mb-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold mr-1">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['pnr', 'train_id', 'status']))
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings Table Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-clipboard-list text-primary mr-1"></i> All Passenger Reservations
                </h3>
                <span class="badge badge-info">{{ $bookings->total() }} Total Records</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0 text-nowrap">
                    <thead class="thead-dark">
                        <tr>
                            <th>PNR Code</th>
                            <th>Booked By</th>
                            <th>Passenger Details</th>
                            <th>Train & Route</th>
                            <th>Journey Date</th>
                            <th>Seat</th>
                            <th>Fare</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td class="font-weight-bold text-primary">{{ $booking->booking_code }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $booking->user->name }}</div>
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
                                    <small class="text-muted">{{ $booking->trainSchedule->formatted_departure_time }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $booking->seat->coach }}-{{ $booking->seat->seat_number }} ({{ $booking->seat->seat_class }})
                                    </span>
                                </td>
                                <td class="font-weight-bold text-success">{{ $booking->formatted_fare }}</td>
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
                                    <i class="fas fa-ticket-alt fa-3x mb-2 text-secondary"></i>
                                    <p class="mb-0">No booking records found.</p>
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
