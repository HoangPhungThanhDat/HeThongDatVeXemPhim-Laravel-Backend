<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class TicketRepository
{
    // Cache 1 phút để đảm bảo dữ liệu vé luôn mới
    const CACHE_TIME = 60; 

    /**
     * Lấy tất cả Ticket - ✅ ĐÃ CACHE
     */
    public function all()
    {
        return Cache::remember('tickets_all', self::CACHE_TIME, function () {
            return Ticket::with([
                // ⭐ ĐÃ THÊM 'Price' VÀO ĐÂY
                'showtime:ShowtimeId,MovieId,RoomId,StartTime,Price', 
                'showtime.movie:MovieId,Title,PosterUrl', 
                'showtime.room:RoomId,Name',
                'seat:SeatId,Row,Number,SeatType',
                'user:UserId,FullName,Email' 
            ])->orderBy('CreatedAt', 'desc')->get();
        });
    }

    /**
     * Tìm Ticket theo ID - ✅ ĐÃ CACHE
     */
    public function find($TicketId)
    {
        return Cache::remember("ticket_detail_{$TicketId}", self::CACHE_TIME, function () use ($TicketId) {
            return Ticket::with([
                'showtime.movie',
                'showtime.room',
                'seat',
                'user',
                'orderDetails.item' 
            ])->find($TicketId);
        });
    }

    /**
     * Tạo mới Ticket - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $ticket = Ticket::create($data);
        $this->clearCache();
        return $ticket;
    }

    /**
     * Cập nhật Ticket - ✅ XÓA CACHE
     */
    public function update(Ticket $ticket, array $data)
    {
        $ticket->update($data);
        $this->clearCache($ticket->TicketId);
        return $ticket;
    }

    /**
     * Xóa Ticket - ✅ XÓA CACHE
     */
    public function delete(Ticket $ticket)
    {
        $id = $ticket->TicketId;
        $result = $ticket->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan (Đã tối ưu)
     */
    public function clearCache($id = null)
    {
        // ⭐ Xóa cache danh sách vé
        Cache::forget('tickets_all');
        
        // ⭐ Xóa cache chi tiết của vé đó (nếu có truyền ID)
        if ($id) {
            Cache::forget("ticket_detail_{$id}");
        }

        // ⭐ (Tùy chọn) Xóa cache dashboard vé nếu có dùng
        Cache::forget('ticket_management_dashboard');
    }
}