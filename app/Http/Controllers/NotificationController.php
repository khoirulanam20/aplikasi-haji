<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Notifications/Index', [
            'notifications' => $request->user()->notifications()->paginate($request->integer('per_page', 10)),
        ]);
    }

    public function markAsRead(string $notificationId, Request $request): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notificationId)->update(['read_at' => now()]);

        return back();
    }
}
