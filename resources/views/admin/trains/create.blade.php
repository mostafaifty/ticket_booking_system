@extends('layouts.master')

@section('title', 'Add New Train')
@section('page_title', 'Register New Train')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.trains.index') }}">Trains</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-plus-circle mr-1 text-success"></i> Add Train to Fleet
                </h3>
            </div>
            <form method="POST" action="{{ route('admin.trains.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Train Number -->
                        <div class="col-md-5 mb-3">
                            <label for="train_number" class="font-weight-bold">
                                Train Number <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="text" 
                                       name="train_number" 
                                       id="train_number" 
                                       class="form-control @error('train_number') is-invalid @enderror" 
                                       placeholder="e.g., 701, 703, 813" 
                                       value="{{ old('train_number') }}" 
                                       maxlength="20" 
                                       required>
                                @error('train_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Official railway service code / number.</small>
                        </div>

                        <!-- Train Name -->
                        <div class="col-md-7 mb-3">
                            <label for="train_name" class="font-weight-bold">
                                Train Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-train"></i></span>
                                </div>
                                <input type="text" 
                                       name="train_name" 
                                       id="train_name" 
                                       class="form-control @error('train_name') is-invalid @enderror" 
                                       placeholder="e.g., Subarna Express" 
                                       value="{{ old('train_name') }}" 
                                       required>
                                @error('train_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Full registered service name.</small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Train Type -->
                        <div class="col-md-4 mb-3">
                            <label for="train_type" class="font-weight-bold">
                                Train Service Type <span class="text-danger">*</span>
                            </label>
                            <select name="train_type" id="train_type" class="form-control @error('train_type') is-invalid @enderror" required>
                                <option value="Intercity" {{ old('train_type', 'Intercity') === 'Intercity' ? 'selected' : '' }}>Intercity Express</option>
                                <option value="Mail/Express" {{ old('train_type') === 'Mail/Express' ? 'selected' : '' }}>Mail / Express</option>
                                <option value="Commuter" {{ old('train_type') === 'Commuter' ? 'selected' : '' }}>Commuter / Local</option>
                            </select>
                            @error('train_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Total Seats -->
                        <div class="col-md-4 mb-3">
                            <label for="total_seats" class="font-weight-bold">
                                Total Seat Capacity <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-chair"></i></span>
                                </div>
                                <input type="number" 
                                       name="total_seats" 
                                       id="total_seats" 
                                       class="form-control @error('total_seats') is-invalid @enderror" 
                                       placeholder="e.g., 400" 
                                       value="{{ old('total_seats', 0) }}" 
                                       min="0" 
                                       max="2000" 
                                       required>
                                @error('total_seats')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Rated passenger capacity.</small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4 mb-3">
                            <label for="status" class="font-weight-bold">
                                Fleet Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (In Operation)</option>
                                <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance / Repair</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (Retired)</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('admin.trains.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Trains
                    </a>
                    <button type="submit" class="btn btn-success font-weight-bold px-4">
                        <i class="fas fa-save mr-1"></i> Create Train
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
