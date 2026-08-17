@extends('layouts.master')

@section('title', 'Station Details - ' . $station->name)
@section('page_title', 'Station Profile & Schedules')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.stations.index') }}">Stations</a></li>
    <li class="breadcrumb-item active">{{ $station->code }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Station Information Summary Card -->
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body box-profile text-center py-4">
                <div class="mb-3">
                    <span class="fa-stack fa-3x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-train fa-stack-1x fa-inverse"></i>
                    </span>
                </div>
                <h3 class="profile-username font-weight-bold text-dark mb-1">{{ $station->name }}</h3>
                <p class="text-muted mb-2">
                    <span class="badge badge-secondary px-3 py-1 font-weight-bold text-sm">Station Code: {{ $station->code }}</span>
                </p>
                <div class="mb-3">
                    @if($station->status === 'active')
                        <span class="badge badge-success px-3 py-1"><i class="fas fa-check-circle mr-1"></i> Active (In Service)</span>
                    @else
                        <span class="badge badge-secondary px-3 py-1"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                    @endif
                </div>

                <ul class="list-group list-group-unbordered text-left mb-4">
                    <li class="list-group-item">
                        <b><i class="fas fa-map-marker-alt text-danger mr-2"></i> Location</b> 
                        <span class="float-right text-muted">{{ $station->location ?? 'Not Specified' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-sign-out-alt text-info mr-2"></i> Total Departures</b> 
                        <span class="float-right badge badge-info">{{ $station->departure_schedules_count }} schedules</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-sign-in-alt text-success mr-2"></i> Total Arrivals</b> 
                        <span class="float-right badge badge-success">{{ $station->arrival_schedules_count }} schedules</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-calendar-alt text-secondary mr-2"></i> Registered Date</b> 
                        <span class="float-right text-muted">{{ $station->created_at->format('M d, Y') }}</span>
                    </li>
                </ul>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.stations.index') }}" class="btn btn-secondary btn-block mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <a href="{{ route('admin.stations.edit', $station) }}" class="btn btn-primary btn-block mt-0">
                        <i class="fas fa-edit mr-1"></i> Edit Station
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Departure & Arrival Schedules Tables -->
    <div class="col-md-8">
        <!-- Recent Departure Schedules -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-plane-departure mr-1 text-info"></i> Recent Departures From This Station
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Train</th>
                            <th>Destination Station</th>
                            <th>Departure Time</th>
                            <th>Journey Date</th>
                            <th>Fare (BDT)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departureSchedules as $sched)
                            <tr>
                                <td class="font-weight-bold text-dark">
                                    {{ $sched->train->train_name ?? 'N/A' }} <small class="text-muted">#{{ $sched->train->train_number ?? '' }}</small>
                                </td>
                                <td>{{ $sched->arrivalStation->name ?? 'N/A' }}</td>
                                <td><i class="far fa-clock text-info mr-1"></i> {{ date('h:i A', strtotime($sched->departure_time)) }}</td>
                                <td>{{ $sched->journey_date ? $sched->journey_date->format('M d, Y') : 'N/A' }}</td>
                                <td class="font-weight-bold text-success">৳{{ number_format($sched->fare, 2) }}</td>
                                <td><span class="badge badge-info text-capitalize">{{ $sched->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">
                                    No departure schedules recorded for this station yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Arrival Schedules -->
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-plane-arrival mr-1 text-success"></i> Recent Arrivals Destined To This Station
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Train</th>
                            <th>Origin Station</th>
                            <th>Arrival Time</th>
                            <th>Journey Date</th>
                            <th>Fare (BDT)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arrivalSchedules as $sched)
                            <tr>
                                <td class="font-weight-bold text-dark">
                                    {{ $sched->train->train_name ?? 'N/A' }} <small class="text-muted">#{{ $sched->train->train_number ?? '' }}</small>
                                </td>
                                <td>{{ $sched->departureStation->name ?? 'N/A' }}</td>
                                <td><i class="far fa-clock text-success mr-1"></i> {{ date('h:i A', strtotime($sched->arrival_time)) }}</td>
                                <td>{{ $sched->journey_date ? $sched->journey_date->format('M d, Y') : 'N/A' }}</td>
                                <td class="font-weight-bold text-success">৳{{ number_format($sched->fare, 2) }}</td>
                                <td><span class="badge badge-info text-capitalize">{{ $sched->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">
                                    No arrival schedules recorded for this station yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
