<?php

namespace App\Http\Controllers\Api;

use App\Models\Seat;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSeatRequest;
use App\Http\Requests\BulkStoreSeatRequest;
use App\Http\Resources\SeatResource;
use App\Http\Controllers\Controller;
use App\Services\SeatService;

class SeatController extends Controller
{
    protected $seatService;

    public function __construct(SeatService $seatService)
    {
        $this->seatService = $seatService;
    }

    /**
     * Lấy tất cả ghế
     */
    public function index()
    {
        try {
            $seats = $this->seatService->getAll();
            return response()->json([
                'success' => true,
                'data' => SeatResource::collection($seats)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách ghế',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo ghế đơn lẻ
     */
    public function store(StoreSeatRequest $request)
    {
        try {
            $seat = $this->seatService->create($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Tạo ghế thành công',
                'data' => new SeatResource($seat)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo ghế',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo nhiều ghế cùng lúc
     */
    public function bulkStore(BulkStoreSeatRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $seats = $this->seatService->bulkCreate($validatedData['seats']);
            
            return response()->json([
                'success' => true,
                'message' => 'Tạo thành công ' . count($seats) . ' ghế và cập nhật số ghế phòng',
                'data' => SeatResource::collection($seats),
                'count' => count($seats)
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo ghế',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết ghế
     */
    public function show($SeatId)
    {
        try {
            $seat = $this->seatService->getById($SeatId);
            
            return response()->json([
                'success' => true,
                'data' => new SeatResource($seat)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ghế',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Cập nhật ghế
     */
    public function update(StoreSeatRequest $request, $SeatId)
    {
        try {
            $seat = $this->seatService->update($SeatId, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật ghế thành công',
                'data' => new SeatResource($seat)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật ghế',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa ghế
     */
    public function destroy($SeatId)
    {
        try {
            $this->seatService->delete($SeatId);
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa ghế thành công và cập nhật số ghế phòng'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa ghế',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa tất cả ghế của một phòng
     * 
     */
    public function deleteByRoom($roomId)
    {
        try {
            
            $this->seatService->deleteByRoomId($roomId);

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa toàn bộ ghế trong phòng và cập nhật số ghế về 0'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa ghế',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}