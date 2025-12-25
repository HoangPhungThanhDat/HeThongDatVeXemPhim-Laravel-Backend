<?php

namespace App\Services;

use App\Repositories\BannerRepository;
use Illuminate\Support\Facades\Auth;

class BannerService
{
    protected $bannerRepository;

    public function __construct(BannerRepository $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function getAll()
    {
        return $this->bannerRepository->getAll();
    }

    public function getById($id)
    {
        return $this->bannerRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->bannerRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->bannerRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->bannerRepository->delete($id);
    }
}
