<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;

/**
 * Lightweight read-only lookup for the mobile app's "who is this for"
 * audience pickers (meeting/task creation) — Department is global so every
 * authenticated user sees the full list.
 */
class OrgDirectoryController extends Controller
{
    public function departments()
    {
        return response()->json(['data' => Department::orderBy('name')->get(['id', 'name'])]);
    }
}
