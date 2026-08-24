<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->applicableTo($request->user())
            ->with('creator')
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('user.announcements.index', ['announcements' => $announcements]);
    }
}
