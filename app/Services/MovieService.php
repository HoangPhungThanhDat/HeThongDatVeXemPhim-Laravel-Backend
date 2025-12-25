<?php

namespace App\Services;

use App\Repositories\MovieRepository;
use Illuminate\Support\Facades\Auth;
class MovieService
{
    protected $repository;

    public function __construct(MovieRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($MovieId)
    {
        return $this->repository->find($MovieId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($MovieId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $movie = $this->repository->find($MovieId);
        if (!$movie) {
            return null;
        }
        return $this->repository->update($movie, $data);
    }

    public function delete($MovieId)
    {
        $movie = $this->repository->find($MovieId);
        if (!$movie) {
            return null;
        }
        return $this->repository->delete($movie);
    }
}
