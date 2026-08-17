@extends('layouts.master')

@section('title', 'Edit Train Schedule #' . $schedule->id)
@section('page_title', 'Edit Train Schedule #' . $schedule->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
    <li class="breadcrumb-item active">Edit #{{ $schedule->id }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-edit text-primary mr-1"></i> Update Schedule Information
                </h3>
            </div>
            
            <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <!-- Train Selection -->
                        <div class="col-md-6 form-group">
                            <label for="train_id" class="font-weight-bold">
                                Select Train <span class="text-danger">*</span>
                            </label>
                            <select name="train_id" id="train_id" class="form-control @error('train_id') is-invalid @enderror" required>
                                <option value="">-- Choose Operating Train --</option>
                                @foreach($trains as $train)
                                    <option value="{{ $train->id }}" {{ old('train_id', $schedule->train_id) == $train->id ? 'selected' : '' }}>
                                        {{ $train->train_number }} - {{ $train->train_name }} ({{ $train->train_type }} | {{ $train->total_seats }} Seats)
                                    </option>
                                @endforeach
                            </select>
                            @error('train_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status Selection -->
                        <div class="col-md-6 form-group">
                            <label for="status" class="font-weight-bold">
                                Schedule Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                @php $currentStatus = old('status', $schedule->status); @endphp
                                <option value="scheduled" {{ $currentStatus === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="delayed" {{ $currentStatus === 'delayed' ? 'selected' : '' }}>Delayed</option>
                                <option value="departed" {{ $currentStatus === 'departed' ? 'selected' : '' }}>Departed</option>
                                <option value="completed" {{ $currentStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Departure Station -->
                        <div class="col-md-6 form-group">
                            <label for="departure_station_id" class="font-weight-bold">
                                Origin / Departure Station <span class="text-danger">*</span>
                            </label>
                            <select name="departure_station_id" id="departure_station_id" class="form-control @error('departure_station_id') is-invalid @enderror" required>
                                <option value="">-- Choose Origin Station --</option>
                                @foreach($stations as $station)
                                    <option value="{{ $station->id }}" {{ old('departure_station_id', $schedule->departure_station_id) == $station->id ? 'selected' : '' }}>
                                        {{ $station->name }} ({{ $station->code }}) - {{ $station->location }}
                                    </option>
                                @endforeach
                            </select>
                            @error('departure_station_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Arrival Station -->
                        <div class="col-md-6 form-group">
                            <label for="arrival_station_id" class="font-weight-bold">
                                Destination / Arrival Station <span class="text-danger">*</span>
                            </label>
                            <select name="arrival_station_id" id="arrival_station_id" class="form-control @error('arrival_station_id') is-invalid @enderror" required>
                                <option value="">-- Choose Destination Station --</option>
                                @foreach($stations as $station)
                                    <option value="{{ $station->id }}" {{ old('arrival_station_id', $schedule->arrival_station_id) == $station->id ? 'selected' : '' }}>
                                        {{ $station->name }} ({{ $station->code }}) - {{ $station->location }}
                                    </option>
                                @endforeach
                            </select>
                            @error('arrival_station_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Journey Date -->
                        <div class="col-md-4 form-group">
                            <label for="journey_date" class="font-weight-bold">
                                Journey Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   name="journey_date" 
                                   id="journey_date" 
                                   value="{{ old('journey_date', $schedule->journey_date ? $schedule->journey_date->format('Y-m-d') : '') }}" 
                                   class="form-control @error('journey_date') is-invalid @enderror" 
                                   required>
                            @error('journey_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Departure Time -->
                        <div class="col-md-4 form-group">
                            <label for="departure_time" class="font-weight-bold">
                                Departure Time (24h) <span class="text-danger">*</span>
                            </label>
                            <input type="time" 
                                   name="departure_time" 
                                   id="departure_time" 
                                   value="{{ old('departure_time', substr($schedule->departure_time, 0, 5)) }}" 
                                   class="form-control @error('departure_time') is-invalid @enderror" 
                                   required>
                            @error('departure_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Arrival Time -->
                        <div class="col-md-4 form-group">
                            <label for="arrival_time" class="font-weight-bold">
                                Arrival Time (24h) <span class="text-danger">*</span>
                            </label>
                            <input type="time" 
                                   name="arrival_time" 
                                   id="arrival_time" 
                                   value="{{ old('arrival_time', substr($schedule->arrival_time, 0, 5)) }}" 
                                   class="form-control @error('arrival_time') is-invalid @enderror" 
                                   required>
                            @error('arrival_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fare -->
                        <div class="col-md-6 form-group">
                            <label for="fare" class="font-weight-bold">
                                Ticket Fare (BDT / ৳) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold">৳</span>
                                </div>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="fare" 
                                       id="fare" 
                                       value="{{ old('fare', $schedule->fare) }}" 
                                       class="form-control @error('fare') is-invalid @enderror" 
                                       required>
                                @error('fare')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary font-weight-bold">
                        <i class="fas fa-arrow-left mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Update Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
