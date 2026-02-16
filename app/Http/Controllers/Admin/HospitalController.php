<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    public function datatables(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('manage_hospitals'), 403);

        $draw = (int) $request->integer('draw', 1);
        $start = max(0, (int) $request->integer('start', 0));
        $length = max(1, (int) $request->integer('length', 10));
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));

        $columns = [
            0 => 'name',
            1 => 'town',
            2 => 'type',
            3 => 'is_active',
            4 => 'created_at',
        ];

        $orderIndex = (int) data_get($request->input('order'), '0.column', 0);
        $orderColumn = $columns[$orderIndex] ?? 'name';
        $orderDir = strtolower((string) data_get($request->input('order'), '0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $baseQuery = Hospital::query()->select(['id', 'name', 'town', 'type', 'is_active', 'created_at']);
        $recordsTotal = (clone $baseQuery)->count();

        if ($search !== '') {
            $needle = '%' . $search . '%';
            $baseQuery->where(function ($query) use ($needle): void {
                $query->whereRaw('LOWER(name) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(town, \'\')) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(type, \'\')) LIKE ?', [$needle]);
            });
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $rows = $baseQuery
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (Hospital $hospital) {
            return [
                'id' => $hospital->id,
                'name' => e($hospital->name),
                'town' => e($hospital->town ?: '—'),
                'type' => e($hospital->type ?: '—'),
                'status' => $hospital->is_active
                    ? '<span class="badge bg-soft-success text-success">Active</span>'
                    : '<span class="badge bg-light text-muted">Inactive</span>',
                'actions' => view('admin.hospitals.partials.actions', ['row' => $hospital])->render(),
            ];
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}

