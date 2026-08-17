@extends('layouts.master')

@section('title', 'Station Management')
@section('page_title', 'Station Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Stations</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter & Search Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Search & Filter Stations
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.stations.create') }}" class="btn btn-success btn-sm font-weight-bold">
                        <i class="fas fa-plus mr-1"></i> Add New Station
                    </a>
                </div>
            </div>
            <div class="card-body bg-light">
                <form method="GET" action="{{ route('admin.stations.index') }}">
                    <div class="row">
                        <div class="col-md-6 col-lg-5 mb-2">
                            <label for="search" class="text-sm font-weight-bold">Keyword Search</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, code, or location..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3 mb-2">
                            <label for="status" class="text-sm font-weight-bold">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-lg-4 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.stations.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stations List Card -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-map-marker-alt mr-1"></i> Railway Stations ({{ $stations->total() }})
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 120px;">Code</th>
                            <th>Station Name</th>
                            <th>Location</th>
                            <th class="text-center" style="width: 140px;">Departures</th>
                            <th class="text-center" style="width: 140px;">Arrivals</th>
                            <th class="text-center" style="width: 110px;">Status</th>
                            <th class="text-center" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stations as $index => $station)
                            <tr>
                                <td class="align-middle text-muted">{{ $stations->firstItem() + $index }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-secondary px-2 py-1 font-weight-bold text-sm">
                                        {{ $station->code }}
                                    </span>
                                </td>
                                <td class="align-middle font-weight-bold text-dark">
                                    {{ $station->name }}
                                </td>
                                <td class="align-middle text-muted">
                                    <i class="fas fa-map-pin text-danger mr-1"></i> {{ $station->location ?? 'N/A' }}
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-info px-2 py-1">
                                        {{ $station->departure_schedules_count }} schedules
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-info px-2 py-1">
                                        {{ $station->arrival_schedules_count }} schedules
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    @if($station->status === 'active')
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.stations.show', $station) }}" class="btn btn-info" title="View Station Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.stations.edit', $station) }}" class="btn btn-primary" title="Edit Station">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger btn-delete-station" 
                                                data-id="{{ $station->id }}"
                                                data-name="{{ $station->name }}"
                                                data-code="{{ $station->code }}"
                                                data-url="{{ route('admin.stations.destroy', $station) }}"
                                                title="Delete Station">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle fa-3x mb-3 text-secondary"></i>
                                        <h5>No railway stations found</h5>
                                        <p class="mb-3">Try adjusting your search criteria or register a new station.</p>
                                        <a href="{{ route('admin.stations.create') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus mr-1"></i> Create First Station
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($stations->hasPages())
                <div class="card-footer clearfix bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ $stations->firstItem() }} to {{ $stations->lastItem() }} of {{ $stations->total() }} stations
                        </small>
                        <div>
                            {{ $stations->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteStationModal" tabindex="-1" role="dialog" aria-labelledby="deleteStationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="deleteStationForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="deleteStationModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Confirm Station Deletion
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-1 text-dark">Are you sure you want to permanently delete this station?</p>
                    <div class="alert alert-warning mb-0 mt-3">
                        <strong id="deleteStationTargetName">Station Name</strong> (<span id="deleteStationTargetCode">CODE</span>)
                        <div class="small mt-1 text-muted">
                            Note: Deletion will be rejected if any train schedules reference this station.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger font-weight-bold">
                        <i class="fas fa-trash-alt mr-1"></i> Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('.btn-delete-station').on('click', function () {
            var url = $(this).data('url');
            var name = $(this).data('name');
            var code = $(this).data('code');

            $('#deleteStationForm').attr('action', url);
            $('#deleteStationTargetName').text(name);
            $('#deleteStationTargetCode').text(code);

            $('#deleteStationModal').modal('show');
        });
    });
</script>
@endpush
