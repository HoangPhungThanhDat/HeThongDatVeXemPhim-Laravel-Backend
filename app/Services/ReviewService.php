<?php

namespace App\Services;

use App\Repositories\ReviewRepository;
use Illuminate\Support\Facades\Auth;
class ReviewService
{
    protected $repository;

    public function __construct(ReviewRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($ReviewId)
    {
        return $this->repository->find($ReviewId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($ReviewId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $review = $this->repository->find($ReviewId);
        if (!$review) {
            return null;
        }
        return $this->repository->update($review, $data);
    }

    public function delete($ReviewId)
    {
        $review = $this->repository->find($ReviewId);
        if (!$review) {
            return null;
        }
        return $this->repository->delete($review);
    }
}
