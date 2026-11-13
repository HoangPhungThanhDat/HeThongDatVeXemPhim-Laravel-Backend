<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Lấy danh sách payment cho admin
     */
    public function getAllPayments()
    {
        return $this->paymentRepository->getAllWithRelations();
    }

    /**
     * Tạo URL thanh toán VNPay + lưu order/ticket/orderdetail
     */
    public function createPaymentUrl(array $bookingInfo, float $amount, string $ipAddr): array
    {
        $this->paymentRepository->beginTransaction();

        try {
            $userId = Auth::id() ?? 2;

            // Lấy giá vé từ showtime
            $showtime = $this->paymentRepository->findShowtime((int) $bookingInfo['ShowtimeId']);
            $ticketPrice = $showtime ? $showtime->Price : 0;

            // 1. Tạo Order
            $order = $this->paymentRepository->createOrder([
                'UserId'      => $userId,
                'OrderDate'   => Carbon::now(),
                'TotalAmount' => $amount,
                'Status'      => 'Pending',
            ]);

            // 2. Tạo Ticket + OrderDetail + cập nhật ShowtimeSeat
            foreach ($bookingInfo['seats'] as $seatId) {
                $ticket = $this->paymentRepository->createTicket([
                    'ShowtimeId'  => $bookingInfo['ShowtimeId'],
                    'SeatId'      => $seatId,
                    'UserId'      => $userId,
                    'BookingTime' => Carbon::now(),
                    'Status'      => 'Pending',
                ]);

                $this->paymentRepository->createOrderDetail([
                    'OrderId'  => $order->OrderId,
                    'TicketId' => $ticket->TicketId,
                    'ItemId'   => null,
                    'Quantity' => 1,
                    'Price'    => $ticketPrice,
                    'Status'   => 'Active',
                ]);

                $this->paymentRepository->updateShowtimeSeatStatus(
                    (int) $bookingInfo['ShowtimeId'],
                    (int) $seatId,
                    'Reserved'
                );
            }

            // 3. Tạo OrderDetail cho combo
            if (!empty($bookingInfo['combos'])) {
                foreach ($bookingInfo['combos'] as $combo) {
                    $this->paymentRepository->createOrderDetail([
                        'OrderId'  => $order->OrderId,
                        'TicketId' => null,
                        'ItemId'   => $combo['id'],
                        'Quantity' => $combo['quantity'],
                        'Price'    => $combo['price'],
                        'Status'   => 'Active',
                    ]);
                }
            }

            $this->paymentRepository->commit();

            // 4. Tạo URL VNPay
            return $this->buildVnpayUrl($order, $amount, $ipAddr);

        } catch (\Exception $e) {
            $this->paymentRepository->rollBack();
            Log::error('Lỗi tạo thanh toán: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xử lý IPN từ VNPay
     */
    public function handleIpn(array $inputData): array
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');

        if (!isset($inputData['vnp_SecureHash'])) {
            return ['RspCode' => '99', 'Message' => 'Missing checksum'];
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);

        $hashData = $this->buildHashData($inputData);
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash !== $vnp_SecureHash) {
            return ['RspCode' => '97', 'Message' => 'Invalid Checksum'];
        }

        $orderId = explode('_', $inputData['vnp_TxnRef'])[0];
        $order = $this->paymentRepository->findOrder((int) $orderId);

        if ($order && $order->Status == 'Pending') {
            if ($inputData['vnp_ResponseCode'] == '00') {
                $this->processSuccessPayment($order);
            } else {
                $this->processFailedPayment($order);
            }
        }

        return ['RspCode' => '00', 'Message' => 'Confirm Success'];
    }

    /**
     * Kiểm tra trạng thái thanh toán (frontend gọi)
     */
    public function checkPaymentStatus(string $txnRef, ?string $responseCode): array
    {
        $orderId = explode('_', $txnRef)[0];
        $order = $this->paymentRepository->findOrder((int) $orderId);

        if (!$order) {
            return ['status' => 'error', 'message' => 'Không tìm thấy đơn hàng'];
        }

        if ($order->Status === 'Pending') {
            if ($responseCode == '00') {
                $this->processSuccessPayment($order);
            } else {
                $this->processFailedPayment($order);
            }
        }

        return ['status' => 'success', 'order_status' => $order->Status];
    }

    // ==========================================
    // PRIVATE HELPERS
    // ==========================================

    /**
     * Build URL thanh toán VNPay
     */
    private function buildVnpayUrl($order, float $amount, string $ipAddr): array
    {
        $vnp_Url        = config('services.vnpay.url');
        $vnp_Returnurl  = config('services.vnpay.return_url');
        $vnp_TmnCode    = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');

        $vnp_TxnRef     = $order->OrderId . '_' . time();
        $vnp_OrderInfo  = "Thanh toan ve xem phim don hang " . $order->OrderId;
        $vnp_OrderType  = 'billpayment';
        $vnp_Amount     = $amount * 100;
        $vnp_Locale     = 'vn';

        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $vnp_TmnCode,
            "vnp_Amount"     => $vnp_Amount,
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $ipAddr,
            "vnp_Locale"     => $vnp_Locale,
            "vnp_OrderInfo"  => $vnp_OrderInfo,
            "vnp_OrderType"  => $vnp_OrderType,
            "vnp_ReturnUrl"  => $vnp_Returnurl,
            "vnp_TxnRef"     => $vnp_TxnRef,
        ];

        ksort($inputData);

        $query = '';
        foreach ($inputData as $key => $value) {
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;

        if (isset($vnp_HashSecret)) {
            $hashData = $this->buildHashData($inputData);
            $vnpSecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return [
            'payment_url' => $vnp_Url,
            'OrderId'     => $order->OrderId,
        ];
    }

    /**
     * Build chuỗi hashdata từ mảng input (dùng chung cho IPN và tạo URL)
     */
    private function buildHashData(array $inputData): string
    {
        $hashData = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        return $hashData;
    }

    /**
     * Xử lý thanh toán thành công
     */
    private function processSuccessPayment($order): void
    {
        $this->paymentRepository->beginTransaction();
        try {
            $this->paymentRepository->updateOrder($order, ['Status' => 'Paid']);

            $orderDetails  = $this->paymentRepository->getOrderDetailsByOrderId($order->OrderId);
            $firstTicketId = null;

            foreach ($orderDetails as $detail) {
                if ($detail->TicketId) {
                    if (!$firstTicketId) {
                        $firstTicketId = $detail->TicketId;
                    }

                    $ticket = $this->paymentRepository->findTicket($detail->TicketId);
                    if ($ticket) {
                        $this->paymentRepository->updateTicket($ticket, ['Status' => 'Paid']);
                        $this->paymentRepository->updateShowtimeSeatStatus(
                            $ticket->ShowtimeId,
                            $ticket->SeatId,
                            'Reserved'
                        );
                    }
                }
            }

            // Chỉ tạo 1 payment duy nhất (chống lặp dữ liệu)
            if ($firstTicketId) {
                $this->paymentRepository->updateOrCreatePayment($firstTicketId, [
                    'Amount'        => $order->TotalAmount,
                    'PaymentMethod' => 'VNPAY',
                    'PaymentStatus' => 'Success',
                    'PaymentDate'   => Carbon::now(),
                    'Status'        => 'Active',
                ]);
            }

            $this->paymentRepository->commit();
        } catch (\Exception $e) {
            $this->paymentRepository->rollBack();
            Log::error('Lỗi xử lý đơn thành công: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý thanh toán thất bại
     */
    private function processFailedPayment($order): void
    {
        $this->paymentRepository->beginTransaction();
        try {
            $this->paymentRepository->updateOrder($order, ['Status' => 'Cancelled']);

            $orderDetails = $this->paymentRepository->getOrderDetailsByOrderId($order->OrderId);

            foreach ($orderDetails as $detail) {
                if ($detail->TicketId) {
                    $ticket = $this->paymentRepository->findTicket($detail->TicketId);
                    if ($ticket) {
                        $this->paymentRepository->updateTicket($ticket, ['Status' => 'Cancelled']);
                        $this->paymentRepository->updateShowtimeSeatStatus(
                            $ticket->ShowtimeId,
                            $ticket->SeatId,
                            'Available'
                        );
                    }
                }
            }

            $this->paymentRepository->commit();
        } catch (\Exception $e) {
            $this->paymentRepository->rollBack();
            Log::error('Lỗi hủy đơn: ' . $e->getMessage());
        }
    }
}