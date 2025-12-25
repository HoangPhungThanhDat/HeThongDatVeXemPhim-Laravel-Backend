<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Http\Requests\StoreContactRequest;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $payments = $this->paymentService->getAll();
        return PaymentResource::collection($payments);
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = $this->paymentService->create($request->validated());
        return new PaymentResource($payment);
    }

    public function show($PaymentId)
    {
        $payment = $this->paymentService->getById($PaymentId);
        return new PaymentResource($payment);
    }

    public function update(StoreContactRequest $request, $PaymentId)
    {
        $payment = $this->paymentService->update($PaymentId, $request->validated());
        return new PaymentResource($payment);
    }

    public function destroy($PaymentId)
    {
        $this->paymentService->delete($PaymentId);
        return response()->json(['message' => 'payment deleted successfully']);
    }
}