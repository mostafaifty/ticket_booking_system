@extends('layouts.master')

@section('title', 'Admin Dashboard')
@section('page_title', 'Administrative Overview')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Small Stat Boxes -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_trains'] ?? 0 }}</h3>
                <p>Registered Trains</p>
            </div>
            <div class="icon">
                <i class="fas fa-subway"></i>
            </div>
            <a href="#" class="small-box-footer">
                Manage Trains (Inc. 3) <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_stations'] ?? 0 }}</h3>
                <p>Railway Stations</p>
            </div>
            <div class="icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <a href="#" class="small-box-footer">
                Manage Stations (Inc. 3) <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning shadow-sm">
            <div class="inner">
                <h3>{{ $stats['active_schedules'] ?? 0 }}</h3>
                <p>Active Schedules</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="#" class="small-box-footer">
                Manage Schedules (Inc. 4) <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger shadow-sm">
            <div class="inner">
                <h3>{{ $stats['total_bookings'] ?? 0 }}</h3>
                <p>Total Bookings</p>
            </div>
            <div class="icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <a href="#" class="small-box-footer">
                View All Bookings (Inc. 8) <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Admin Quick Action Cards -->
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-tools mr-1"></i> Admin Management Modules
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Welcome to the Railway System Administrator Control Panel. From here you can manage stations, configure train fleets, assign schedules, and review passenger bookings.
                </p>
                <div class="list-group">
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                            <strong>Station Management</strong>
                            <div class="text-xs text-muted">Create, edit, and organize train terminals and junction points.</div>
                        </div>
                        <span class="badge badge-secondary badge-pill">Increment 3</span>
                    </div>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-subway text-success mr-2"></i>
                            <strong>Train Fleet & Coaches</strong>
                            <div class="text-xs text-muted">Configure train names, types, compartments, and seat layouts.</div>
                        </div>
                        <span class="badge badge-secondary badge-pill">Increment 3</span>
                    </div>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-calendar-check text-warning mr-2"></i>
                            <strong>Route Schedules & Fares</strong>
                            <div class="text-xs text-muted">Set up departure/arrival times and class-based ticket pricing.</div>
                        </div>
                        <span class="badge badge-secondary badge-pill">Increment 4</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-info card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-shield-alt mr-1"></i> System Status & Environment
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle mb-0">
                    <tbody>
                        <tr>
                            <td><strong>Environment</strong></td>
                            <td><span class="badge badge-success">{{ app()->environment() }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Laravel Version</strong></td>
                            <td>{{ app()->version() }}</td>
                        </tr>
                        <tr>
                            <td><strong>PHP Version</strong></td>
                            <td>{{ PHP_VERSION }}</td>
                        </tr>
                        <tr>
                            <td><strong>Database Engine</strong></td>
                            <td>{{ config('database.default') }} (MySQL)</td>
                        </tr>
                        <tr>
                            <td><strong>Logged-in Admin</strong></td>
                            <td>{{ auth()->user()->name }} ({{ auth()->user()->email }})</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
