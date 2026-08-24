<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Notifications\AnnouncementPublished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $announcements = Announcement::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->applicableTo($user)
            ->with('creator')
            ->orderByDesc('published_at')
            ->paginate($request->integer('per_page', 20));

        $readIds = $user->notifications()
            ->whereNotNull('read_at')
            ->pluck('data')
            ->pluck('announcement_id')
            ->flip();

        $announcements->getCollection()->each(
            fn (Announcement $announcement) => $announcement->setAttribute('is_read', $readIds->has($announcement->id))
        );

        return AnnouncementResource::collection($announcements);
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

        return new AnnouncementResource($announcement);
    }

    public function markRead(Request $request, Announcement $announcement)
    {
        $request->user()->unreadNotifications()
            ->get()
            ->filter(fn ($n) => ($n->data['announcement_id'] ?? null) === $announcement->id)
            ->each->markAsRead();

        return response()->json(['message' => 'Marked as read.']);
    }
}
