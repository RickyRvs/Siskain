<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit(Request $request)
    {
        $tenant = $request->user()->tenant;

        return view('owner.settings.edit', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'logo' => 'nullable|image|max:2048',
        ]);

        foreach (['name', 'title', 'primary_color'] as $field) {
            $tenant->updateSetting($field, $validated[$field] ?? null, $request->user()->id);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('tenant-logos', 'public');
            $tenant->updateSetting('logo_path', $path, $request->user()->id);
        }

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}