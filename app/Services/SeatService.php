<?php

namespace App\Services;

use App\Repositories\SeatRepository;
use Illuminate\Support\Facades\Auth;

class SeatService
{
    protected $seatRepository;

    public function __construct(SeatRepository $seatRepository)
    {
        $this->seatRepository = $seatRepository;
    }

    public function getAll()
    {
        return $this->seatRepository->getAll();
    }

    public function getById($id)
    {
        return $this->seatRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->seatRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->seatRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->seatRepository->delete($id);
    }
}
