<?php

namespace App\Services;

use App\Repositories\ContactRepository;
use Illuminate\Support\Facades\Auth;

class ContactService
{
    protected $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function getAll()
    {
        return $this->contactRepository->getAll();
    }

    public function getById($id)
    {
        return $this->contactRepository->getById($id);
    }

    public function create(array $data)
    {
        $user = Auth::user();
        $data['CreatedBy'] = $user ? $user->UserId : $data['UserId'];
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();
        return $this->contactRepository->create($data);
    }

    public function update($id, array $data)
    {
        $user = Auth::user();
        $data['UpdatedBy'] = $user ? $user->UserId : null;
        $data['UpdatedAt'] = now();
        return $this->contactRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->contactRepository->delete($id);
    }
}