<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\PharmacyRespondOrderRequest;
use App\Models\Order;
use App\Models\OrderPharmacyRequest;
use App\Services\OrderRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderPharmacyRequestController extends Controller
{
    public function index(Request $request, OrderRoutingService $routingService)
    {
        $pharmacyId = $request->user()->pharmacy_id;

        $pending = OrderPharmacyRequest::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('status', OrderPharmacyRequest::STATUS_SENT)
            ->with(['order.hospital'])
            ->orderBy('created_at')
            ->paginate(20);

        $pending->getCollection()->each(function (OrderPharmacyRequest $item) use ($routingService) {
            $routingService->markTimedOutRequests($item->order);
        });

        return response()->json(
            OrderPharmacyRequest::query()
                ->where('pharmacy_id', $pharmacyId)
                ->where('status', OrderPharmacyRequest::STATUS_SENT)
                ->with(['order.hospital'])
                ->orderBy('created_at')
                ->paginate(20)
        );
    }

    public function respond(PharmacyRespondOrderRequest $request, OrderPharmacyRequest $orderPharmacyRequest)
    {
        $pharmacyId = $request->user()->pharmacy_id;
        if ($orderPharmacyRequest->pharmacy_id !== $pharmacyId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $incoming = $request->validated('status');

        $updated = DB::transaction(function () use ($orderPharmacyRequest, $incoming) {
            $opr = OrderPharmacyRequest::query()->lockForUpdate()->findOrFail($orderPharmacyRequest->id);

            if ($opr->status !== OrderPharmacyRequest::STATUS_SENT) {
                return null;
            }

            $order = Order::query()->lockForUpdate()->findOrFail($opr->order_id);
            if ($order->status === Order::STATUS_CANCELLED) {
                return null;
            }

            if ($incoming === OrderPharmacyRequest::STATUS_IN_STOCK) {
                $opr->update([
                    'status' => OrderPharmacyRequest::STATUS_IN_STOCK,
                    'responded_at' => now(),
                ]);

                $order->update([
                    'status' => Order::STATUS_MATCHED,
                    'matched_pharmacy_id' => $opr->pharmacy_id,
                ]);

                OrderPharmacyRequest::query()
                    ->where('order_id', $order->id)
                    ->where('id', '!=', $opr->id)
                    ->where('status', OrderPharmacyRequest::STATUS_SENT)
                    ->update([
                        'status' => OrderPharmacyRequest::STATUS_EXPIRED,
                        'responded_at' => now(),
                    ]);
            } else {
                $opr->update([
                    'status' => OrderPharmacyRequest::STATUS_OUT_OF_STOCK,
                    'responded_at' => now(),
                ]);

                $hasPending = OrderPharmacyRequest::query()
                    ->where('order_id', $order->id)
                    ->where('status', OrderPharmacyRequest::STATUS_SENT)
                    ->exists();

                if (! $hasPending && $order->status !== Order::STATUS_MATCHED) {
                    $order->update(['status' => Order::STATUS_UNAVAILABLE_NEARBY]);
                }
            }

            return $opr->fresh()->load(['order.hospital', 'pharmacy']);
        });

        if (! $updated) {
            return response()->json(['message' => 'Unable to apply response.'], 422);
        }

        return response()->json($updated);
    }
}
