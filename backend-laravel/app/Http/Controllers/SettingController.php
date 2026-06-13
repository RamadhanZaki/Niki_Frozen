<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private function checkOwner(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        return null;
    }

    /** GET /api/settings */
    public function index(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $settings = Setting::orderBy('id')->get()->mapWithKeys(fn($s) => [
            $s->key => [
                'value' => $s->value,
                'label' => $s->label,
                'type'  => $s->type,
            ]
        ]);

        return response()->json(['settings' => $settings]);
    }

    /** PUT /api/settings */
    public function update(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'settings'       => 'required|array',
            'settings.*.key' => 'required|string',
        ]);

        foreach ($request->settings as $item) {
            Setting::set($item['key'], $item['value'] ?? '');
        }

        return response()->json(['message' => 'Pengaturan berhasil disimpan.']);
    }
}
