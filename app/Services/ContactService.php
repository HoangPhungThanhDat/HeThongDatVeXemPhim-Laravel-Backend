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
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->contactRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->contactRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->contactRepository->delete($id);
    }
}
