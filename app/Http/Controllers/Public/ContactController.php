<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::where('group', 'general')
            ->get()
            ->keyBy('key');

        return Inertia::render('Contact', [
            'settings' => $settings,
        ]);
    }
}