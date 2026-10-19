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
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();
        return $this->repository->create($data);
    }

    public function update($TicketId, array $data)
    {
        $ticket = $this->repository->find($TicketId);
        if (!$ticket) {
            return null;
        }

        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        
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