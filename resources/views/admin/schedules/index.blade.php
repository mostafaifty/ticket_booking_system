@extends('layouts.master')

@section('title', 'Manage Train Schedules')
@section('page_title', 'Train Schedules')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Schedules</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter & Action Card -->
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Filter Schedules
                </h3>
                <div class="card-tools ml-auto">
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-success btn-sm font-weight-bold">
                        <i class="fas fa-plus-circle mr-1"></i> Create New Schedule
                    </a>
                </div>
            </div>
            <div class="card-body bg-light">
                <form method="GET" action="{{ route('admin.schedules.index') }}" class="row">
                    <div class="col-md-3 form-group mb-2">
                        <label class="text-sm">Assigned Train</label>
                        <select name="train_id" class="form-control form-control-sm">
                            <option value="">-- All Trains --</option>
                            @foreach($trains as $train)
                                <option value="{{ $train->id }}" {{ request('train_id') == $train->id ? 'selected' : '' }}>
                                    {{ $train->train_number }} - {{ $train->train_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 form-group mb-2">
                        <label class="text-sm">From Station</label>
                        <select name="departure_station_id" class="form-control form-control-sm">
                            <option value="">-- Any Station --</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->id }}" {{ request('departure_station_id') == $station->id ? 'selected' : '' }}>
                                    {{ $station->name }} ({{ $station->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 form-group mb-2">
                        <label class="text-sm">To Station</label>
                        <select name="arrival_station_id" class="form-control form-control-sm">
                            <option value="">-- Any Station --</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->id }}" {{ request('arrival_station_id') == $station->id ? 'selected' : '' }}>
                                    {{ $station->name }} ({{ $station->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 form-group mb-2">
                        <label class="text-sm">Journey Date</label>
                        <input type="date" name="journey_date" value="{{ request('journey_date') }}" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2 form-group mb-2">
                        <label class="text-sm">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">-- All Statuses --</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="delayed" {{ request('status') == 'delayed' ? 'selected' : '' }}>Delayed</option>
                            <option value="departed" {{ request('status') == 'departed' ? 'selected' : '' }}>Departed</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-1 form-group mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedule Table Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h3 class="card-title font-weight-bold text-dark">
                    <i class="fas fa-calendar-alt text-primary mr-1"></i> Timetable List
                </h3>
                <span class="badge badge-info">{{ $schedules->total() }} Total Schedules</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0 text-nowrap">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 70px;">#ID</th>
                            <th>Train</th>
                            <th>Route (Origin &rarr; Destination)</th>
                            <th>Date & Time</th>
                            <th>Fare</th>
                            <th>Seats (Avail / Total)</th>
                            <th>Status</th>
                            <th class="text-right" style="width: 170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td class="font-weight-bold text-muted">#{{ $schedule->id }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $schedule->train->train_name }}</div>
                                    <small class="badge badge-secondary">{{ $schedule->train->train_number }} ({{ $schedule->train->train_type }})</small>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                        <span class="font-weight-bold">{{ $schedule->departureStation->name }}</span>
                                    </div>
                                    <div class="text-muted small">
                                        &darr; to <span class="font-weight-bold text-dark">{{ $schedule->arrivalStation->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="far fa-calendar-alt text-info mr-1"></i>
                                        <strong>{{ $schedule->formatted_journey_date }}</strong>
                                    </div>
                                    <small class="text-muted">
                                        <i class="far fa-clock mr-1"></i> {{ $schedule->formatted_departure_time }} &rarr; {{ $schedule->formatted_arrival_time }}
                                    </small>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-success">৳ {{ number_format($schedule->fare, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $schedule->available_seats_count > 0 ? 'badge-success' : 'badge-danger' }} p-1 px-2">
                                        {{ $schedule->available_seats_count }} / {{ $schedule->train->total_seats }} Left
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $schedule->status_badge_class }} text-uppercase px-2 py-1">
                                        {{ $schedule->status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.schedules.show', $schedule) }}" class="btn btn-info btn-xs" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary btn-xs" title="Edit Schedule">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete Schedule #{{ $schedule->id }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" title="Delete Schedule">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-2 text-secondary"></i>
                                    <p class="mb-0">No train schedules found matching your search criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($schedules->hasPages())
                <div class="card-footer clearfix bg-white">
                    <div class="float-right">
                        {{ $schedules->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
