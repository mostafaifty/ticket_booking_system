@extends('layouts.master')

@section('title', 'Train Management')
@section('page_title', 'Train Fleet Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Trains</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter & Search Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Search & Filter Trains
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.trains.create') }}" class="btn btn-success btn-sm font-weight-bold">
                        <i class="fas fa-plus mr-1"></i> Add New Train
                    </a>
                </div>
            </div>
            <div class="card-body bg-light">
                <form method="GET" action="{{ route('admin.trains.index') }}">
                    <div class="row">
                        <div class="col-md-4 col-lg-4 mb-2">
                            <label for="search" class="text-sm font-weight-bold">Keyword Search</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search by train name or number..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-3 col-lg-3 mb-2">
                            <label for="train_type" class="text-sm font-weight-bold">Train Type</label>
                            <select name="train_type" id="train_type" class="form-control">
                                <option value="">All Types</option>
                                <option value="Intercity" {{ request('train_type') === 'Intercity' ? 'selected' : '' }}>Intercity</option>
                                <option value="Mail/Express" {{ request('train_type') === 'Mail/Express' ? 'selected' : '' }}>Mail/Express</option>
                                <option value="Commuter" {{ request('train_type') === 'Commuter' ? 'selected' : '' }}>Commuter</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-lg-2 mb-2">
                            <label for="status" class="text-sm font-weight-bold">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>

                        <div class="col-md-2 col-lg-3 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.trains.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Trains List Card -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-subway mr-1"></i> Train Fleets ({{ $trains->total() }})
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 140px;">Train No.</th>
                            <th>Train Name</th>
                            <th style="width: 150px;">Type</th>
                            <th class="text-center" style="width: 130px;">Total Seats</th>
                            <th class="text-center" style="width: 120px;">Schedules</th>
                            <th class="text-center" style="width: 130px;">Status</th>
                            <th class="text-center" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trains as $index => $train)
                            <tr>
                                <td class="align-middle text-muted">{{ $trains->firstItem() + $index }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-secondary px-2 py-1 font-weight-bold text-sm">
                                        #{{ $train->train_number }}
                                    </span>
                                </td>
                                <td class="align-middle font-weight-bold text-dark">
                                    <i class="fas fa-train text-info mr-1"></i> {{ $train->train_name }}
                                </td>
                                <td class="align-middle">
                                    @if($train->train_type === 'Intercity')
                                        <span class="badge badge-primary px-2 py-1">{{ $train->train_type }}</span>
                                    @elseif($train->train_type === 'Mail/Express')
                                        <span class="badge badge-info px-2 py-1">{{ $train->train_type }}</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1">{{ $train->train_type }}</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center font-weight-bold">
                                    {{ number_format($train->total_seats) }}
                                    @if($train->seats_count > 0)
                                        <small class="text-muted d-block">({{ $train->seats_count }} seats mapped)</small>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-info px-2 py-1">
                                        {{ $train->schedules_count }} runs
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    @if($train->status === 'active')
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                    @elseif($train->status === 'maintenance')
                                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-tools mr-1"></i> Maintenance</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.trains.show', $train) }}" class="btn btn-info" title="View Train Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.trains.edit', $train) }}" class="btn btn-primary" title="Edit Train">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger btn-delete-train" 
                                                data-id="{{ $train->id }}"
                                                data-name="{{ $train->train_name }}"
                                                data-number="{{ $train->train_number }}"
                                                data-url="{{ route('admin.trains.destroy', $train) }}"
                                                title="Delete Train">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-subway fa-3x mb-3 text-secondary"></i>
                                        <h5>No trains found</h5>
                                        <p class="mb-3">Try adjusting your filters or register a new train into the fleet.</p>
                                        <a href="{{ route('admin.trains.create') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus mr-1"></i> Add First Train
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($trains->hasPages())
                <div class="card-footer clearfix bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ $trains->firstItem() }} to {{ $trains->lastItem() }} of {{ $trains->total() }} trains
                        </small>
                        <div>
                            {{ $trains->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTrainModal" tabindex="-1" role="dialog" aria-labelledby="deleteTrainModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="deleteTrainForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="deleteTrainModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Confirm Train Deletion
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-1 text-dark">Are you sure you want to permanently delete this train from the fleet?</p>
                    <div class="alert alert-warning mb-0 mt-3">
                        <strong id="deleteTrainTargetName">Train Name</strong> (#<span id="deleteTrainTargetNumber">701</span>)
                        <div class="small mt-1 text-muted">
                            Note: Deletion will be rejected if any train schedules or seat bookings are attached to this train.
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
        $('.btn-delete-train').on('click', function () {
            var url = $(this).data('url');
            var name = $(this).data('name');
            var number = $(this).data('number');

            $('#deleteTrainForm').attr('action', url);
            $('#deleteTrainTargetName').text(name);
            $('#deleteTrainTargetNumber').text(number);

            $('#deleteTrainModal').modal('show');
        });
    });
</script>
@endpush
