<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Http\Requests\StoreContactRequest;

/**
 * @group Payments
 * 
 * APIs for managing payment transactions.
 * Payments are associated with orders and can be made via various methods
 * (credit card, bank transfer, e-wallet, etc.).
 */
class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get all payments
     * 
     * Get a list of all payment transactions. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "PaymentId": 1,
     *     "OrderId": 1,
     *     "UserId": 1,
     *     "Amount": 150000,
     *     "PaymentMethod": "Credit Card",
     *     "Status": "Completed",
     *     "TransactionId": "TXN-2024-001",
     *     "PaymentDate": "2024-01-01 10:00:00",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "PaymentId": 2,
     *     "OrderId": 2,
     *     "UserId": 2,
     *     "Amount": 250000,
     *     "PaymentMethod": "Bank Transfer",
     *     "Status": "Pending",
     *     "TransactionId": "TXN-2024-002",
     *     "PaymentDate": null,
     *     "CreatedAt": "2024-01-01 10:30:00"
     *   }
     * ]
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function index()
    {
        $payments = $this->paymentService->getAll();
        return PaymentResource::collection($payments);
    }

    /**
     * Create a new payment
     * 
     * Create a new payment transaction. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam OrderId int required The ID of the order being paid. Example: 1
     * @bodyParam UserId int required The ID of the user making payment. Example: 1
     * @bodyParam Amount numeric required The payment amount. Example: 150000
     * @bodyParam PaymentMethod string required The payment method. Enum: Credit Card, Bank Transfer, E-Wallet, Cash. Example: Credit Card
     * @bodyParam Status string The payment status. Enum: Pending, Completed, Failed, Refunded. Example: Completed
     * @bodyParam TransactionId string The transaction ID from payment gateway. Example: TXN-2024-001
     * @bodyParam PaymentDate string The payment date and time. Example: 2024-01-01 10:00:00
     * 
     * @response 201 {
     *   "PaymentId": 1,
     *   "OrderId": 1,
     *   "UserId": 1,
     *   "Amount": 150000,
     *   "PaymentMethod": "Credit Card",
     *   "Status": "Completed",
     *   "TransactionId": "TXN-2024-001",
     *   "PaymentDate": "2024-01-01 10:00:00",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "OrderId": ["The order id field is required."],
     *     "UserId": ["The user id field is required."],
     *     "Amount": ["The amount field is required."],
     *     "PaymentMethod": ["The payment method field is required."]
     *   }
     * }
     */
    public function store(StorePaymentRequest $request)
    {
        $payment = $this->paymentService->create($request->validated());
        return new PaymentResource($payment);
    }

    /**
     * Get a specific payment
     * 
     * Get detailed information about a specific payment transaction. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam PaymentId int required The ID of the payment. Example: 1
     * 
     * @response 200 {
     *   "PaymentId": 1,
     *   "OrderId": 1,
     *   "UserId": 1,
     *   "Amount": 150000,
     *   "PaymentMethod": "Credit Card",
     *   "Status": "Completed",
     *   "TransactionId": "TXN-2024-001",
     *   "PaymentDate": "2024-01-01 10:00:00",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Payment not found"
     * }
     */
    public function show($PaymentId)
    {
        $payment = $this->paymentService->getById($PaymentId);
        return new PaymentResource($payment);
    }

    /**
     * Update a payment
     * 
     * Update an existing payment transaction. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam PaymentId int required The ID of the payment. Example: 1
     * @bodyParam OrderId int The ID of the order. Example: 1
     * @bodyParam UserId int The ID of the user. Example: 1
     * @bodyParam Amount numeric The payment amount. Example: 150000
     * @bodyParam PaymentMethod string The payment method. Enum: Credit Card, Bank Transfer, E-Wallet, Cash. Example: Bank Transfer
     * @bodyParam Status string The payment status. Enum: Pending, Completed, Failed, Refunded. Example: Refunded
     * @bodyParam TransactionId string The transaction ID. Example: TXN-2024-001-UPDATED
     * @bodyParam PaymentDate string The payment date and time. Example: 2024-01-01 10:00:00
     * 
     * @response 200 {
     *   "PaymentId": 1,
     *   "OrderId": 1,
     *   "UserId": 1,
     *   "Amount": 150000,
     *   "PaymentMethod": "Bank Transfer",
     *   "Status": "Refunded",
     *   "TransactionId": "TXN-2024-001-UPDATED",
     *   "PaymentDate": "2024-01-01 10:00:00",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Payment not found"
     * }
     */
    public function update(StoreContactRequest $request, $PaymentId)
    {
        $payment = $this->paymentService->update($PaymentId, $request->validated());
        return new PaymentResource($payment);
    }

    /**
     * Delete a payment
     * 
     * Delete a payment transaction from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam PaymentId int required The ID of the payment. Example: 1
     * 
     * @response 200 {
     *   "message": "Payment deleted successfully"
     * }
     * @response 404 {
     *   "message": "Payment not found"
     * }
     */
    public function destroy($PaymentId)
    {
        $this->paymentService->delete($PaymentId);
        return response()->json(['message' => 'Payment deleted successfully']);
    }
}