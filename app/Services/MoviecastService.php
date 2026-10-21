<?php

namespace App\Services;

use App\Repositories\MoviecastRepository;
use Illuminate\Support\Facades\Auth;

class MoviecastService
{
    protected $repository;

    public function __construct(MoviecastRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * ✅ Lấy dữ liệu phân trang từ Repository
     */
    public function getPaged($page, $limit, $search = '', $role = '', $status = '') // ✅ Thêm $status
    {
        return $this->repository->getPaged($page, $limit, $search, $role, $status);
    }

    public function find($CastId)
    {
        return $this->repository->find($CastId);
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

    public function update($CastId, array $data)
    {
        $moviecast = $this->repository->find($CastId);
        if (!$moviecast) {
            return null;
        }
        
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        
        return $this->repository->update($moviecast, $data);
    }

    public function delete($CastId)
    {
        $moviecast = $this->repository->find($CastId);
        if (!$moviecast) {
            return null;
        }
        return $this->repository->delete($moviecast);
    }
}