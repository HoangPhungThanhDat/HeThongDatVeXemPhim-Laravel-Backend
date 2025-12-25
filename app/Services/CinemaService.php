<?php

namespace App\Services;

use App\Repositories\CinemaRepository;
use Illuminate\Support\Facades\Auth;

class CinemaService
{
    protected $cinemaRepository;

    public function __construct(CinemaRepository $cinemaRepository)
    {
        $this->cinemaRepository = $cinemaRepository;
    }

    public function getAll()
    {
        return $this->cinemaRepository->getAll();
    }

    public function getById($id)
    {
        return $this->cinemaRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->cinemaRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->cinemaRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->cinemaRepository->delete($id);
    }
}
