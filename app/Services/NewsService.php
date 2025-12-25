<?php

namespace App\Services;

use App\Repositories\NewsRepository;
use Illuminate\Support\Facades\Auth;

class NewsService
{
    protected $newsRepository;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    public function getAll()
    {
        return $this->newsRepository->getAll();
    }

    public function getById($id)
    {
        return $this->newsRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->newsRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->newsRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->newsRepository->delete($id);
    }
}
