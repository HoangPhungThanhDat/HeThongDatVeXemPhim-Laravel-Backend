<?php

namespace App\Services;

use App\Repositories\GenreRepository;
use Illuminate\Support\Facades\Auth;

class GenreService
{
    protected $genreRepository;

    public function __construct(GenreRepository $genreRepository)
    {
        $this->genreRepository = $genreRepository;
    }

    public function getAll()
    {
        return $this->genreRepository->getAll();
    }

    public function getById($id)
    {
        return $this->genreRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->genreRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->genreRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->genreRepository->delete($id);
    }
}
