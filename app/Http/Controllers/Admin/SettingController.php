<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['fields' => Setting::FIELDS]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = array_fill_keys(array_keys(Setting::FIELDS), ['nullable', 'string', 'max:255']);
        $rules['email'] = ['nullable', 'email', 'max:255'];
        $rules['proposals_email'] = ['nullable', 'email', 'max:255'];
        foreach (['facebook', 'instagram', 'linkedin'] as $url) {
            $rules[$url] = ['nullable', 'url:https', 'max:255'];
        }
        $rules['rfp_response_hours'] = ['required', 'integer', 'min:1', 'max:168'];
        $rules['vendor_link_days'] = ['required', 'integer', 'min:1', 'max:30'];

        $data = $request->validate($rules);
        Setting::setMany(array_map(fn ($v) => $v === null ? '' : (string) $v, $data));

        return back()->with('status', 'Settings saved. The website shows the new details straight away.');
    }
}
