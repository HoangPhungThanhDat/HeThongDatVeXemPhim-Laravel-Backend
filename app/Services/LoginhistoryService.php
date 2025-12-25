<?php

namespace App\Services;

use App\Repositories\LoginhistoryRepository;
use Illuminate\Support\Facades\Auth;

class LoginhistoryService
{
    protected $LoginhistoryRepository;

    public function __construct(LoginhistoryRepository $LoginhistoryRepository)
    {
        $this->LoginhistoryRepository = $LoginhistoryRepository;
    }

    public function getAll()
    {
        return $this->LoginhistoryRepository->getAll();
    }

    public function getById($id)
    {
        return $this->LoginhistoryRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->LoginhistoryRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->LoginhistoryRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->LoginhistoryRepository->delete($id);
    }
}
