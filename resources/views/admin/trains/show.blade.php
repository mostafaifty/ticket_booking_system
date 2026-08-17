@extends('layouts.master')

@section('title', 'Train Details - ' . $train->train_name)
@section('page_title', 'Train Fleet Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.trains.index') }}">Trains</a></li>
    <li class="breadcrumb-item active">{{ $train->train_number }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Train Profile Overview Card -->
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body box-profile text-center py-4">
                <div class="mb-3">
                    <span class="fa-stack fa-3x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-subway fa-stack-1x fa-inverse"></i>
                    </span>
                </div>
                <h3 class="profile-username font-weight-bold text-dark mb-1">{{ $train->train_name }}</h3>
                <p class="text-muted mb-2">
                    <span class="badge badge-secondary px-3 py-1 font-weight-bold text-sm">Train #{{ $train->train_number }}</span>
                </p>
                <div class="mb-3">
                    @if($train->status === 'active')
                        <span class="badge badge-success px-3 py-1"><i class="fas fa-check-circle mr-1"></i> Active (In Service)</span>
                    @elseif($train->status === 'maintenance')
                        <span class="badge badge-warning text-dark px-3 py-1"><i class="fas fa-tools mr-1"></i> Maintenance</span>
                    @else
                        <span class="badge badge-secondary px-3 py-1"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                    @endif
                </div>

                <ul class="list-group list-group-unbordered text-left mb-4">
                    <li class="list-group-item">
                        <b><i class="fas fa-tag text-info mr-2"></i> Service Type</b> 
                        <span class="float-right font-weight-bold">{{ $train->train_type }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-chair text-primary mr-2"></i> Total Capacity</b> 
                        <span class="float-right font-weight-bold">{{ number_format($train->total_seats) }} seats</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-layer-group text-success mr-2"></i> Mapped Seats</b> 
                        <span class="float-right badge badge-success">{{ $train->seats_count }} seats</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-calendar-alt text-warning mr-2"></i> Assigned Schedules</b> 
                        <span class="float-right badge badge-info">{{ $train->schedules_count }} runs</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-clock text-secondary mr-2"></i> Registered Date</b> 
                        <span class="float-right text-muted">{{ $train->created_at->format('M d, Y') }}</span>
                    </li>
                </ul>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.trains.index') }}" class="btn btn-secondary btn-block mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <a href="{{ route('admin.trains.edit', $train) }}" class="btn btn-primary btn-block mt-0">
                        <i class="fas fa-edit mr-1"></i> Edit Train
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Coach Layout & Schedules Overview -->
    <div class="col-md-8">
        <!-- Coach & Seat Distribution -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-couch mr-1 text-info"></i> Coach & Seat Class Distribution
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Coach ID</th>
                            <th>Seat Class Category</th>
                            <th class="text-center">Seat Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coachBreakdown as $coach)
                            <tr>
                                <td class="font-weight-bold text-dark">
                                    <span class="badge badge-secondary px-2 py-1">Coach {{ $coach->coach }}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-primary">{{ $coach->seat_class }}</span>
                                </td>
                                <td class="text-center font-weight-bold">
                                    {{ $coach->total }} seats
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">
                                    No individual coach/seat layout mapped for this train yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Train Run Schedules -->
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-route mr-1 text-success"></i> Scheduled Journeys For This Train
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Route (From &rarr; To)</th>
                            <th>Departure Time</th>
                            <th>Arrival Time</th>
                            <th>Journey Date</th>
                            <th>Fare</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSchedules as $sched)
                            <tr>
                                <td class="font-weight-bold text-dark">
                                    {{ $sched->departureStation->name ?? 'N/A' }} 
                                    <i class="fas fa-arrow-right text-muted mx-1"></i> 
                                    {{ $sched->arrivalStation->name ?? 'N/A' }}
                                </td>
                                <td>{{ date('h:i A', strtotime($sched->departure_time)) }}</td>
                                <td>{{ date('h:i A', strtotime($sched->arrival_time)) }}</td>
                                <td>{{ $sched->journey_date ? $sched->journey_date->format('M d, Y') : 'N/A' }}</td>
                                <td class="font-weight-bold text-success">৳{{ number_format($sched->fare, 2) }}</td>
                                <td><span class="badge badge-info text-capitalize">{{ $sched->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">
                                    No scheduled journeys currently assigned to this train.
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
