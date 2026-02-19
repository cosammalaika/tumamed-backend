<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->string('q')->toString());
        if ($query === '') {
            $query = trim($request->string('search')->toString());
        }

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = (int) $request->integer('per_page', 50);
        $perPage = max(1, min(100, $perPage));

        $hospitals = Hospital::query()
            ->where('is_active', true)
            ->when($query !== '', function ($builder) use ($query) {
                $needle = '%'.mb_strtolower($query).'%';
                $builder->where(function ($subQuery) use ($needle) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(town) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(address) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(type) LIKE ?', [$needle]);
                });
            })
            ->orderBy('name', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $hospitals->items(),
            'meta' => [
                'page' => $hospitals->currentPage(),
                'per_page' => $hospitals->perPage(),
                'total' => $hospitals->total(),
                'has_more' => $hospitals->hasMorePages(),
            ],
        ]);
    }
}
