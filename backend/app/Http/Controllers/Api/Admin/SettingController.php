<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        return Setting::all()->groupBy('group');
    }

    public function update(Request $r)
    {
        $data = $r->validate(['settings' => 'required|array', 'settings.*.group' => 'required|string', 'settings.*.key' => 'required|string', 'settings.*.value' => 'nullable', 'settings.*.is_public' => 'sometimes|boolean']);
        DB::transaction(fn () => collect($data['settings'])->each(fn ($x) => Setting::updateOrCreate(['key' => $x['key']], $x)));

        return $this->index();
    }
}
