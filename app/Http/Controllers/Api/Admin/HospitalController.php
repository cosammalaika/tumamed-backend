<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HospitalRequest;
use App\Models\Hospital;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $hospitals = Hospital::query()
            ->when($search, function ($query) use ($search) {
                $needle = '%'.strtolower($search).'%';
                $query->where(function ($subQuery) use ($needle) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(town) LIKE ?', [$needle])
                        ->orWhereRaw('LOWER(type) LIKE ?', [$needle]);
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json($hospitals);
    }

    public function store(HospitalRequest $request, AuditLogger $auditLogger)
    {
        $hospital = Hospital::create($request->validated());

        $auditLogger->log('ADMIN_CREATE_HOSPITAL', $hospital->toArray(), $hospital, null, $request);

        return response()->json($hospital, 201);
    }

    public function show(Hospital $hospital)
    {
        return response()->json($hospital);
    }

    public function update(HospitalRequest $request, Hospital $hospital, AuditLogger $auditLogger)
    {
        $hospital->update($request->validated());

        $auditLogger->log('ADMIN_UPDATE_HOSPITAL', $hospital->toArray(), $hospital, null, $request);

        return response()->json($hospital);
    }

    public function destroy(Hospital $hospital, AuditLogger $auditLogger, Request $request)
    {
        $auditLogger->log('ADMIN_DELETE_HOSPITAL', $hospital->toArray(), $hospital, null, $request);
        $hospital->delete();

        return response()->noContent();
    }
}
