<?php
namespace App\Repositories;
use App\Models\Ticket;
class TicketRepository
{
    public function all()
    {
        return Ticket::with(['showtime', 'seat', 'user'])->get();
    }
    public function find($TicketId)
    {
        return Ticket::with(['showtime', 'seat', 'user'])->find($TicketId);
    }
     public function create(array $data)
    {
        return Ticket::create($data);
    }

    public function update(Ticket $ticket, array $data)
    {
        $ticket->update($data);
        return $ticket;
    }

    public function delete(Ticket $ticket)
    {
        return $ticket->delete();
    }
   

   
}