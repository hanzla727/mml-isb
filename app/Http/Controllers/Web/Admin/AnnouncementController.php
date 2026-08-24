<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use Illuminate\Support\Facades\Notification;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::with('creator')->orderByDesc('created_at')->paginate(20),
            'departments' => Department::orderBy('name')->get(),
            'teams' => Team::orderBy('name')->get(),
            'users' => User::role('user')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $announcement = Announcement::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'published_at' => $request->input('published_at') ?? now(),
        ]);

        if ($announcement->published_at <= now()) {
            Notification::send($announcement->audienceUsers()->get(), new AnnouncementPublished($announcement));
        }

        return back()->with('status', 'Announcement published.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('status', 'Announcement deleted.');
    }
}
