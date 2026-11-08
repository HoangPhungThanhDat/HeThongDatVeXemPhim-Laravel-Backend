<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Payment;
use App\Models\Orderdetail;
use App\Models\Showtimeseat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentController extends Controller
{
    // ==========================================
    // 1. API TẠO URL THANH TOÁN VNPAY
    // ==========================================
    public function createPaymentUrl(Request $request)
    {
        // Validate dữ liệu từ Frontend gửi lên
        $request->validate([
            'amount' => 'required|numeric',
            'booking_info' => 'required|array',
            'booking_info.ShowtimeId' => 'required|integer',
            'booking_info.seats' => 'required|array', // Mảng các SeatId
            'booking_info.combos' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $userId = Auth::id() ?? 1; // Tạm thời lấy user id 1 nếu chưa login

            // 1. Tạo Order (Trạng thái Pending)
            $order = Order::create([
                'UserId' => $userId,
                'OrderDate' => Carbon::now(),
                'TotalAmount' => $request->amount,
                'Status' => 'Pending',
            ]);

            // 2. Lưu Vé (Tickets) và OrderDetails
            foreach ($request->booking_info['seats'] as $seatId) {
                // Tạo vé
                $ticket = Ticket::create([
                    'ShowtimeId' => $request->booking_info['ShowtimeId'],
                    'SeatId' => $seatId,
                    'UserId' => $userId,
                    'BookingTime' => Carbon::now(),
                    'Status' => 'Pending',
                ]);

                // Liên kết Vé vào Đơn hàng qua bảng OrderDetails
                // Vì cấu trúc của bạn dùng Orderdetail để chứa cả Vé và Combo
                Orderdetail::create([
                    'OrderId' => $order->OrderId,
                    'TicketId' => $ticket->TicketId,
                    'ItemId' => null,
                    'Quantity' => 1,
                    'Price' => 0, // Giá vé có thể cập nhật sau, hoặc lấy từ Showtime
                    'Status' => 'Active',
                ]);

                // Tạm khóa ghế (Reserved) để người khác không đặt được
                Showtimeseat::where('ShowtimeId', $request->booking_info['ShowtimeId'])
                    ->where('SeatId', $seatId)
                    ->update(['Status' => 'Reserved']);
            }

            // 3. Lưu Chi tiết Combo (nếu có)
            if (!empty($request->booking_info['combos'])) {
                foreach ($request->booking_info['combos'] as $combo) {
                    Orderdetail::create([
                        'OrderId' => $order->OrderId,
                        'TicketId' => null,
                        'ItemId' => $combo['id'],
                        'Quantity' => $combo['quantity'],
                        'Price' => $combo['price'],
                        'Status' => 'Active',
                    ]);
                }
            }

            DB::commit();

            // ==========================================
            // 4. TẠO URL VNPAY
            // ==========================================
            $vnp_Url = config('services.vnpay.url');
            $vnp_Returnurl = config('services.vnpay.return_url');
            $vnp_TmnCode = config('services.vnpay.tmn_code');
            $vnp_HashSecret = config('services.vnpay.hash_secret');

            $vnp_TxnRef = $order->OrderId . '_' . time(); // Mã đơn hàng duy nhất
            $vnp_OrderInfo = "Thanh toan ve xem phim don hang " . $order->OrderId;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $request->amount * 100; // VNPAY nhân 100
            $vnp_Locale = 'vn';
            $vnp_IpAddr = $request->ip();

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            );

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            return response()->json([
                'status' => 'success',
                'payment_url' => $vnp_Url,
                'OrderId' => $order->OrderId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo thanh toán: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 2. API XỬ LÝ IPN (Webhook VNPAY gọi về)
    // ==========================================
    public function vnpayIpn(Request $request)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $inputData = array();
        $data = $request->all();

        foreach ($data as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        if (!isset($inputData['vnp_SecureHash'])) {
            return response()->json(['RspCode' => '99', 'Message' => 'Missing checksum']);
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        // Kiểm tra chữ ký bảo mật
        if ($secureHash == $vnp_SecureHash) {
            // Lấy OrderId từ vnp_TxnRef (VD: "12_16999999" -> 12)
            $order_id = explode('_', $inputData['vnp_TxnRef'])[0];
            $order = Order::find($order_id);

            if ($order && $order->Status == 'Pending') {
                if ($inputData['vnp_ResponseCode'] == '00') {
                    // THANH TOÁN THÀNH CÔNG
                    DB::beginTransaction();
                    try {
                        // 1. Cập nhật Order
                        $order->update(['Status' => 'Paid']);
                        
                        // 2. Lấy tất cả các OrderDetail thuộc đơn hàng này
                        $orderDetails = Orderdetail::where('OrderId', $order->OrderId)->get();

                        foreach ($orderDetails as $detail) {
                            // Nếu là Vé (TicketId không null)
                            if ($detail->TicketId) {
                                $ticket = Ticket::find($detail->TicketId);
                                if ($ticket) {
                                    $ticket->update(['Status' => 'Paid']);
                                    
                                    // Cập nhật trạng thái Ghế (Giữ nguyên Reserved hoặc chuyển thành Booked nếu bạn có trạng thái này)
                                    Showtimeseat::where('ShowtimeId', $ticket->ShowtimeId)
                                        ->where('SeatId', $ticket->SeatId)
                                        ->update(['Status' => 'Reserved']); 
                                    
                                    // Tạo bản ghi Payment cho vé này
                                    Payment::create([
                                        'TicketId' => $ticket->TicketId,
                                        'Amount' => $detail->Price, // Hoặc giá vé thực tế
                                        'PaymentMethod' => 'VNPAY',
                                        'PaymentStatus' => 'Success',
                                        'PaymentDate' => Carbon::now(),
                                        'Status' => 'Active',
                                    ]);
                                }
                            }
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Lỗi IPN: ' . $e->getMessage());
                        return response()->json(['RspCode' => '99', 'Message' => 'Error updating DB']);
                    }
                } else {
                    // Thanh toán thất bại -> Hủy đơn, nhả ghế
                    DB::beginTransaction();
                    try {
                        $order->update(['Status' => 'Cancelled']);
                        $orderDetails = Orderdetail::where('OrderId', $order->OrderId)->get();
                        
                        foreach ($orderDetails as $detail) {
                            if ($detail->TicketId) {
                                $ticket = Ticket::find($detail->TicketId);
                                if ($ticket) {
                                    $ticket->update(['Status' => 'Cancelled']);
                                    Showtimeseat::where('ShowtimeId', $ticket->ShowtimeId)
                                        ->where('SeatId', $ticket->SeatId)
                                        ->update(['Status' => 'Available']);
                                }
                            }
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Lỗi hủy đơn IPN: ' . $e->getMessage());
                    }
                }
            }
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } else {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid Checksum']);
        }
    }

    // ==========================================
    // Hàm xử lý Return URL (Dành cho Frontend gọi để verify)
    // ==========================================
    public function vnpayReturn(Request $request)
    {
        // Hàm này có thể dùng chung logic với IPN hoặc chỉ trả về kết quả
        // Để đơn giản, Frontend có thể dựa vào URL params để hiển thị thông báo
        return response()->json(['status' => 'success', 'message' => 'Return URL received']);
    }
}