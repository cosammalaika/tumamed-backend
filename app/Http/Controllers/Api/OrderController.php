<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpandOrderSearchRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UploadOrderPrescriptionsRequest;
use App\Models\Order;
use App\Models\OrderPharmacyRequest;
use App\Models\OrderPrescription;
use App\Services\OrderRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, OrderRoutingService $routingService)
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($request, $data) {
            return Order::create([
                'user_id' => $request->user()->id,
                'hospital_id' => $data['hospital_id'],
                'user_lat' => $data['user_lat'] ?? null,
                'user_lng' => $data['user_lng'] ?? null,
                'is_self_patient' => $data['is_self_patient'],
                'patient_name' => $data['patient_name'],
                'patient_phone' => $data['patient_phone'],
                'status' => Order::STATUS_SUBMITTED,
                'search_radius_km' => $data['search_radius_km'] ?? 5,
            ]);
        });

        $routingService->dispatch($order->refresh());

        return response()->json($order->load(['hospital', 'matchedPharmacy']), 201);
    }

    public function uploadPrescriptions(UploadOrderPrescriptionsRequest $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        foreach ($request->file('files', []) as $file) {
            $path = $file->store('order-prescriptions', 'public');
            OrderPrescription::create([
                'order_id' => $order->id,
                'file_path' => $path,
                'mime' => $file->getMimeType() ?: 'application/octet-stream',
            ]);
        }

        return response()->json($order->fresh()->load('prescriptions'));
    }

    public function show(Request $request, Order $order, OrderRoutingService $routingService)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $routingService->markTimedOutRequests($order);
        $routingService->refreshAggregateStatus($order->fresh());

        return response()->json($order->fresh()->load(['hospital', 'prescriptions', 'matchedPharmacy']));
    }

    public function requests(Request $request, Order $order, OrderRoutingService $routingService)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $routingService->markTimedOutRequests($order);
        $routingService->refreshAggregateStatus($order->fresh());

        $rows = $order->pharmacyRequests()
            ->with('pharmacy')
            ->orderByDesc('score')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function expandSearch(ExpandOrderSearchRequest $request, Order $order, OrderRoutingService $routingService)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! in_array($order->status, [Order::STATUS_UNAVAILABLE_NEARBY, Order::STATUS_EXPANDED_SEARCH], true)) {
            return response()->json(['message' => 'Order is not eligible for search expansion.'], 422);
        }

        $newRadius = (int) $request->validated('radius_km');
        if ($newRadius <= (int) $order->search_radius_km) {
            return response()->json(['message' => 'New radius must be greater than current radius.'], 422);
        }

        $order->update([
            'search_radius_km' => $newRadius,
            'status' => Order::STATUS_EXPANDED_SEARCH,
        ]);

        $routingService->dispatch($order->fresh());

        return response()->json($order->fresh()->load(['hospital', 'matchedPharmacy']));
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED], true)) {
            return response()->json(['message' => 'Order cannot be cancelled in its current state.'], 422);
        }

        DB::transaction(function () use ($order) {
            $order->pharmacyRequests()
                ->where('status', OrderPharmacyRequest::STATUS_SENT)
                ->update([
                    'status' => OrderPharmacyRequest::STATUS_EXPIRED,
                    'responded_at' => now(),
                ]);

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'matched_pharmacy_id' => null,
            ]);
        });

        return response()->json($order->fresh()->load(['hospital', 'matchedPharmacy']));
    }
}
