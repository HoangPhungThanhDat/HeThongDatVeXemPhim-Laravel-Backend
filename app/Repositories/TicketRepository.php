<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class TicketRepository
{
    // Cache ngắn vì vé thay đổi liên tục (khách đặt, thanh toán...)
    const CACHE_TIME = 60; // 1 phút

    /**
     * Lấy tất cả Ticket - ✅ ĐÃ CACHE
     */
    public function all()
    {
        return Cache::remember('tickets_all', self::CACHE_TIME, function () {
            return Ticket::with([
                'showtime.movie', 
                'showtime.room', 
                'seat', 
                'user'
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
                'user'
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
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('tickets_all');
        if ($id) {
            Cache::forget("ticket_detail_{$id}");
        }
    }
}