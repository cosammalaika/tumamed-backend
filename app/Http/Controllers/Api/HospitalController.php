<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $hospitals = Hospital::query()
            ->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $needle = '%'.strtolower($search).'%';
                $query->where(function ($subQuery) use ($needle) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(town) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(type) LIKE ?', [$needle]);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return response()->json($hospitals);
    }
}
