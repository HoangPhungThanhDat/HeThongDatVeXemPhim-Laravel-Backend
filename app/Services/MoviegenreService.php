<?php

namespace App\Services;

use App\Repositories\MoviegenreRepository;
use Illuminate\Support\Facades\Auth;
class MoviegenreService
{
    protected $repository;

    public function __construct(MoviegenreRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($MovieGenreId)
    {
        return $this->repository->find($MovieGenreId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($MovieGenreId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $moviegenre = $this->repository->find($MovieGenreId);
        if (!$moviegenre) {
            return null;
        }
        return $this->repository->update($moviegenre, $data);
    }

    public function delete($MovieGenreId)
    {
        $moviegenre = $this->repository->find($MovieGenreId);
        if (!$moviegenre) {
            return null;
        }
        return $this->repository->delete($moviegenre);
    }
}
