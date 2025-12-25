<?php

namespace App\Services;

use App\Repositories\WishlistRepository;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    protected $wishlistRepository;

    public function __construct(WishlistRepository $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
    }

    public function getAll()
    {
        return $this->wishlistRepository->getAll();
    }

    public function getById($id)
    {
        return $this->wishlistRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->wishlistRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->wishlistRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->wishlistRepository->delete($id);
    }
}
