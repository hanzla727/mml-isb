<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VolunteerDirectoryController extends Controller
{
    /**
     * Lightweight list of other active volunteers, for meeting-participant
     * pickers. Deliberately not gated behind manage-users — any authenticated
     * user needs this to add participants to a meeting.
     */
    public function index(Request $request)
    {
        $volunteers = User::role('user')
            ->where('is_active', true)
            ->where('id', '!=', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $volunteers]);
    }
}
