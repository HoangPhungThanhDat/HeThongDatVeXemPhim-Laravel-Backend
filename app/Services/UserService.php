<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
class UserService
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($UserId)
    {
        return $this->repository->find($UserId);
    }

    public function create(array $data)
    
    {
        $data['PasswordHash'] = Hash::make($data['PasswordHash']); // Mã hoá mật khẩu
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($UserId, array $data)
    {
        
    if (isset($data['PasswordHash'])) {
        $data['PasswordHash'] = Hash::make($data['PasswordHash']);
    }
        $data['UpdatedBy'] = Auth::user()->UserId;
        $user = $this->repository->find($UserId);
        if (!$user) {
            return null;
        }
        return $this->repository->update($user, $data);
    }

    public function delete($UserId)
    {
        $user = $this->repository->find($UserId);
        if (!$user) {
            return null;
        }
        return $this->repository->delete($user);
    }
}