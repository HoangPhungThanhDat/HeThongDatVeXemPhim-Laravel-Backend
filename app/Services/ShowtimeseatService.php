<?php

namespace App\Services;

use App\Repositories\ShowtimeseatRepository;
use Illuminate\Support\Facades\Auth;

class ShowtimeseatService
{
    protected $showtimeseatRepository;

    public function __construct(ShowtimeseatRepository $showtimeseatRepository)
    {
        $this->showtimeseatRepository = $showtimeseatRepository;
    }

    public function getAll()
    {
        return $this->showtimeseatRepository->getAll();
    }

    public function getById($id)
    {
        return $this->showtimeseatRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->showtimeseatRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->showtimeseatRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->showtimeseatRepository->delete($id);
    }
}
