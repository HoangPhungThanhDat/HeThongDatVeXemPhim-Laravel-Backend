<?php

namespace App\Services;

use App\Repositories\TicketRepository;
use Illuminate\Support\Facades\Auth;
class TicketService
{
    protected $repository;

    public function __construct(TicketRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($TicketId)
    {
        return $this->repository->find($TicketId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($TicketId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $ticket = $this->repository->find($TicketId);
        if (!$ticket) {
            return null;
        }
        return $this->repository->update($ticket, $data);
    }

    public function delete($TicketId)
    {
        $ticket = $this->repository->find($TicketId);
        if (!$ticket) {
            return null;
        }
        return $this->repository->delete($ticket);
    }
}
