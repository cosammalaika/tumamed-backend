<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicineRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = MedicineRequest::query()
            ->with(['patient', 'hospital', 'currentPharmacy', 'events'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('hospital_id'), fn ($query) => $query->where('hospital_id', $request->string('hospital_id')))
            ->when(
                $request->filled('pharmacy_id'),
                fn ($query) => $query->where(function ($sub) use ($request) {
                    $sub->where('current_pharmacy_id', $request->string('pharmacy_id'))
                        ->orWhereHas('assignments', fn ($q) => $q->where('pharmacy_id', $request->string('pharmacy_id')));
                })
            )
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json($requests);
    }
}
