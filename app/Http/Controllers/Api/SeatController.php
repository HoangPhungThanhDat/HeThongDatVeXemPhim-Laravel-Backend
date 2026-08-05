<?php

namespace App\Http\Controllers\Api;

use App\Models\Seat;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSeatRequest;
use App\Http\Requests\BulkStoreSeatRequest;
use App\Http\Resources\SeatResource;
use App\Http\Controllers\Controller;
use App\Services\SeatService;

/**
 * @group Seats
 * 
 * APIs for managing cinema seats.
 * Seats are individual positions in a room where audience members sit.
 * Supports single seat creation, bulk creation, and room-level operations.
 */
class SeatController extends Controller
{
    protected $seatService;

    public function __construct(SeatService $seatService)
    {
        $this->seatService = $seatService;
    }

    /**
     * Get all seats
     * 
     * Get a list of all seats in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "SeatId": 1,
     *       "RoomId": 1,
     *       "Row": "A",
     *       "Number": 1,
     *       "SeatType": "Normal",
     *       "Status": "Available",
     *       "CreatedAt": "2024-01-01 10:00:00"
     *     },
     *     {
     *       "SeatId": 2,
     *       "RoomId": 1,
     *       "Row": "A",
     *       "Number": 2,
     *       "SeatType": "Normal",
     *       "Status": "Available",
     *       "CreatedAt": "2024-01-01 10:00:00"
     *     }
     *   ]
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Lỗi khi lấy danh sách ghế",
     *   "error": "..."
     * }
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
     * Create a single seat
     * 
     * Create a new seat in a room. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam RoomId int required The ID of the room. Example: 1
     * @bodyParam Row string required The row letter. Example: A
     * @bodyParam Number int required The seat number in the row. Example: 1
     * @bodyParam SeatType string The seat type. Enum: Normal, VIP, Couple, Accessible, Sweetbox. Example: Normal
     * @bodyParam Status string The seat status. Enum: Available, Booked, Broken, Inactive. Example: Available
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Tạo ghế thành công",
     *   "data": {
     *     "SeatId": 1,
     *     "RoomId": 1,
     *     "Row": "A",
     *     "Number": 1,
     *     "SeatType": "Normal",
     *     "Status": "Available",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "RoomId": ["The room id field is required."],
     *     "Row": ["The row field is required."],
     *     "Number": ["The number field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Lỗi khi tạo ghế",
     *   "error": "..."
     * }
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
     * Bulk create seats
     * 
     * Create multiple seats at once in a room. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam seats array required The list of seats to create.
     * @bodyParam seats[].Row string required The row letter. Example: A
     * @bodyParam seats[].Number int required The seat number. Example: 1
     * @bodyParam seats[].SeatType string The seat type. Example: Normal
     * @bodyParam seats[].Status string The seat status. Example: Available
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Tạo thành công 10 ghế và cập nhật số ghế phòng",
     *   "data": [...],
     *   "count": 10
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Dữ liệu không hợp lệ",
     *   "errors": {...}
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Có lỗi xảy ra khi tạo ghế",
     *   "error": "..."
     * }
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
     * Get a specific seat
     * 
     * Get detailed information about a specific seat.
     * 
     * @unauthenticated
     * 
     * @urlParam SeatId int required The ID of the seat. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "SeatId": 1,
     *     "RoomId": 1,
     *     "Row": "A",
     *     "Number": 1,
     *     "SeatType": "Normal",
     *     "Status": "Available",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy ghế",
     *   "error": "..."
     * }
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
     * Update a seat
     * 
     * Update a seat's type or status. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam SeatId int required The ID of the seat. Example: 1
     * @bodyParam RoomId int The ID of the room. Example: 1
     * @bodyParam Row string The row letter. Example: A
     * @bodyParam Number int The seat number. Example: 1
     * @bodyParam SeatType string The seat type. Enum: Normal, VIP, Couple, Accessible, Sweetbox. Example: VIP
     * @bodyParam Status string The seat status. Enum: Available, Booked, Broken, Inactive. Example: Broken
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Cập nhật ghế thành công",
     *   "data": {
     *     "SeatId": 1,
     *     "SeatType": "VIP",
     *     "Status": "Broken"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy ghế",
     *   "error": "..."
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Lỗi khi cập nhật ghế",
     *   "error": "..."
     * }
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
     * Delete a seat
     * 
     * Delete a specific seat from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam SeatId int required The ID of the seat. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Xóa ghế thành công và cập nhật số ghế phòng"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy ghế",
     *   "error": "..."
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Lỗi khi xóa ghế",
     *   "error": "..."
     * }
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
     * Delete all seats in a room
     * 
     * Delete all seats belonging to a specific room. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam roomId int required The ID of the room. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Đã xóa toàn bộ ghế trong phòng và cập nhật số ghế về 0"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy phòng",
     *   "error": "..."
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Lỗi khi xóa ghế",
     *   "error": "..."
     * }
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