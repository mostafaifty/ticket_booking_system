<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Passenger\UpdatePasswordRequest;
use App\Http\Requests\Passenger\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the passenger profile page.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('passenger.profile', compact('user'));
    }

    /**
     * Update the passenger's basic profile information.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return redirect()->route('passenger.profile')
            ->with('success', 'Profile information updated successfully!');
    }

    /**
     * Update the passenger's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('passenger.profile')
            ->with('success', 'Password updated successfully!');
    }
}
