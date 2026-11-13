<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Lấy danh sách thanh toán (Admin)
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data'   => $this->paymentService->getAllPayments(),
        ]);
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPaymentUrl(Request $request)
    {
        $request->validate([
            'amount'                       => 'required|numeric',
            'booking_info'                 => 'required|array',
            'booking_info.ShowtimeId'      => 'required|integer',
            'booking_info.seats'           => 'required|array',
            'booking_info.combos'          => 'nullable|array',
        ]);

        try {
            $result = $this->paymentService->createPaymentUrl(
                $request->booking_info,
                (float) $request->amount,
                $request->ip()
            );

            return response()->json([
                'status'      => 'success',
                'payment_url' => $result['payment_url'],
                'OrderId'     => $result['OrderId'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * IPN từ VNPay gọi ngầm
     */
    public function vnpayIpn(Request $request)
    {
        $inputData = array_filter(
            $request->all(),
            fn($key) => substr($key, 0, 4) === 'vnp_',
            ARRAY_FILTER_USE_KEY
        );

        return response()->json($this->paymentService->handleIpn($inputData));
    }

    /**
     * Kiểm tra kết quả thanh toán (Frontend gọi)
     */
    public function checkPaymentStatus(Request $request)
    {
        $txnRef = $request->input('vnp_TxnRef');
        if (!$txnRef) {
            return response()->json(['status' => 'error', 'message' => 'Thiếu mã giao dịch']);
        }

        return response()->json(
            $this->paymentService->checkPaymentStatus(
                $txnRef,
                $request->input('vnp_ResponseCode')
            )
        );
    }
}