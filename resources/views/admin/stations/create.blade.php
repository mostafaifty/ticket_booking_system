@extends('layouts.master')

@section('title', 'Add New Station')
@section('page_title', 'Create Railway Station')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.stations.index') }}">Stations</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-plus-circle mr-1 text-success"></i> Register New Railway Station
                </h3>
            </div>
            <form method="POST" action="{{ route('admin.stations.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Station Name -->
                        <div class="col-md-7 mb-3">
                            <label for="name" class="font-weight-bold">
                                Station Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-train"></i></span>
                                </div>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="e.g., Kamalapur Railway Station" 
                                       value="{{ old('name') }}" 
                                       required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Official station terminal or stop name.</small>
                        </div>

                        <!-- Station Code -->
                        <div class="col-md-5 mb-3">
                            <label for="code" class="font-weight-bold">
                                Station Code <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                </div>
                                <input type="text" 
                                       name="code" 
                                       id="code" 
                                       class="form-control text-uppercase @error('code') is-invalid @enderror" 
                                       placeholder="e.g., DA, CTG, SYL" 
                                       value="{{ old('code') }}" 
                                       maxlength="10" 
                                       required>
                                @error('code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Unique alphanumeric abbreviation (max 10 chars).</small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Location -->
                        <div class="col-md-7 mb-3">
                            <label for="location" class="font-weight-bold">Location / Address</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                </div>
                                <input type="text" 
                                       name="location" 
                                       id="location" 
                                       class="form-control @error('location') is-invalid @enderror" 
                                       placeholder="e.g., Kamalapur, Dhaka-1000" 
                                       value="{{ old('location') }}">
                                @error('location')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">City, district, or street address of the railway terminal.</small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-5 mb-3">
                            <label for="status" class="font-weight-bold">
                                Operational Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (In Service)</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (Closed / Suspended)</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Inactive stations cannot be selected for new routes.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('admin.stations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Stations
                    </a>
                    <button type="submit" class="btn btn-success font-weight-bold px-4">
                        <i class="fas fa-save mr-1"></i> Create Station
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
