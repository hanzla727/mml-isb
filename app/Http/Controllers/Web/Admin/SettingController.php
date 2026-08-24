<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const KEYS = [
        'organization_name' => 'general',
        'organization_email' => 'general',
        'daily_report_reminder_time' => 'notifications',
        'missed_report_grace_hours' => 'notifications',
    ];

    public function index()
    {
        $settings = Setting::whereIn('key', array_keys(self::KEYS))->pluck('value', 'key');

        return view('admin.settings.index', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_email' => ['nullable', 'email'],
            'daily_report_reminder_time' => ['nullable', 'date_format:H:i'],
            'missed_report_grace_hours' => ['nullable', 'integer', 'min:0'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, self::KEYS[$key]);
        }

        return back()->with('status', 'Settings saved.');
    }
}
