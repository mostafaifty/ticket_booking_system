@extends('layouts.master')

@section('title', 'Manage Seats - ' . $train->train_name)
@section('page_title', 'Seat Management - ' . $train->train_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
    <li class="breadcrumb-item active">Seats ({{ $train->train_name }})</li>
@endsection

@section('content')
<div class="row">
    <!-- Train Selector & Summary Header -->
    <div class="col-12 mb-3">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <div class="bg-primary text-white p-3 rounded mr-3">
                        <i class="fas fa-train fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="font-weight-bold mb-0">{{ $train->train_name }} <small class="text-muted">({{ $train->train_number }})</small></h4>
                        <span class="badge badge-info mr-2">{{ $train->train_type }}</span>
                        <span class="badge badge-success">{{ $train->total_seats }} Total Configured Seats</span>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <!-- Train Switcher -->
                    <div class="mr-3">
                        <select class="form-control form-control-sm" onchange="window.location.href='/admin/trains/' + this.value + '/seats'">
                            @foreach($allTrains as $t)
                                <option value="{{ $t->id }}" {{ $t->id == $train->id ? 'selected' : '' }}>
                                    Switch Train: {{ $t->train_name }} ({{ $t->train_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2" data-toggle="modal" data-target="#generateSeatsModal">
                        <i class="fas fa-magic mr-1"></i> Bulk Generate Seats
                    </button>
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#addSeatModal">
                        <i class="fas fa-plus mr-1"></i> Add Single Seat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coaches and Seat Layout -->
<div class="row">
    @forelse($coaches as $coachName => $seats)
        <div class="col-lg-6 mb-4">
            <div class="card card-outline card-secondary shadow-sm h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-couch text-primary mr-1"></i> Coach {{ $coachName }}
                    </h5>
                    <div>
                        <span class="badge badge-primary px-2 py-1 mr-1">
                            {{ $seats->first()->seat_class }}
                        </span>
                        <span class="badge badge-dark">
                            {{ $seats->count() }} Seats
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($seats as $seat)
                            <div class="border rounded p-2 text-center m-1 shadow-xs bg-white" style="min-width: 65px;">
                                <div class="font-weight-bold text-dark">{{ $seat->seat_number }}</div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $seat->coach }}</small>
                                <form action="{{ route('admin.seats.destroy', $seat) }}" method="POST" class="mt-1" onsubmit="return confirm('Delete seat {{ $seat->label }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs py-0 px-1" title="Delete Seat">
                                        <i class="fas fa-times" style="font-size: 0.7rem;"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-chair fa-4x text-muted mb-3"></i>
                    <h4 class="font-weight-bold text-dark">No Seats Configured for {{ $train->train_name }}</h4>
                    <p class="text-muted mx-auto" style="max-width: 500px;">
                        Use the buttons above to bulk generate seats for a coach (e.g. Coach KA, Snigdha, 20 seats) or add individual seats.
                    </p>
                    <button type="button" class="btn btn-success font-weight-bold" data-toggle="modal" data-target="#generateSeatsModal">
                        <i class="fas fa-magic mr-1"></i> Bulk Generate Coach Seats Now
                    </button>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Modal: Add Single Seat -->
<div class="modal fade" id="addSeatModal" tabindex="-1" role="dialog" aria-labelledby="addSeatModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.trains.seats.store', $train) }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="addSeatModalLabel">
                        <i class="fas fa-plus-circle mr-1"></i> Add Single Seat ({{ $train->train_name }})
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="coach" class="font-weight-bold">Coach Name / Code <span class="text-danger">*</span></label>
                        <input type="text" name="coach" id="coach" class="form-control" placeholder="e.g. KA, KHA, GA" required>
                    </div>

                    <div class="form-group">
                        <label for="seat_number" class="font-weight-bold">Seat Number <span class="text-danger">*</span></label>
                        <input type="text" name="seat_number" id="seat_number" class="form-control" placeholder="e.g. 1, 2, A1" required>
                    </div>

                    <div class="form-group">
                        <label for="seat_class" class="font-weight-bold">Seat Class <span class="text-danger">*</span></label>
                        <select name="seat_class" id="seat_class" class="form-control" required>
                            <option value="SHOVON_CHAIR">SHOVON_CHAIR (Shovon Chair)</option>
                            <option value="SNIGDHA">SNIGDHA (AC Chair)</option>
                            <option value="AC_BERTH">AC_BERTH (AC Cabin Berth)</option>
                            <option value="FIRST_CLASS">FIRST_CLASS (First Class)</option>
                            <option value="SHOVON">SHOVON (Non-AC Regular)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Save Seat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Bulk Generate Seats -->
<div class="modal fade" id="generateSeatsModal" tabindex="-1" role="dialog" aria-labelledby="generateSeatsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.trains.seats.generate', $train) }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="generateSeatsModalLabel">
                        <i class="fas fa-magic mr-1"></i> Bulk Generate Coach Seats ({{ $train->train_name }})
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bulk_coach" class="font-weight-bold">Coach Name / Code <span class="text-danger">*</span></label>
                        <input type="text" name="coach" id="bulk_coach" class="form-control" placeholder="e.g. KA, KHA, GA, CHA" required>
                    </div>

                    <div class="form-group">
                        <label for="bulk_seat_class" class="font-weight-bold">Seat Class <span class="text-danger">*</span></label>
                        <select name="seat_class" id="bulk_seat_class" class="form-control" required>
                            <option value="SHOVON_CHAIR">SHOVON_CHAIR (Shovon Chair)</option>
                            <option value="SNIGDHA">SNIGDHA (AC Chair)</option>
                            <option value="AC_BERTH">AC_BERTH (AC Cabin Berth)</option>
                            <option value="FIRST_CLASS">FIRST_CLASS (First Class)</option>
                            <option value="SHOVON">SHOVON (Non-AC Regular)</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 form-group">
                            <label for="seat_count" class="font-weight-bold">Number of Seats <span class="text-danger">*</span></label>
                            <input type="number" name="seat_count" id="seat_count" class="form-control" min="1" max="100" value="20" required>
                        </div>
                        <div class="col-6 form-group">
                            <label for="start_number" class="font-weight-bold">Start Number</label>
                            <input type="number" name="start_number" id="start_number" class="form-control" min="1" value="1" placeholder="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-bold">Generate Seats</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
