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
        $data['PasswordHash'] = Hash::make($data['PasswordHash']);
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

    public function updateProfile($userId, array $data)
    {
        unset($data['PasswordHash']);
        unset($data['Password']);
        unset($data['RoleId']);
        
        $user = $this->repository->find($userId);
        if (!$user) {
            return null;
        }
        
        return $this->repository->update($user, $data);
    }

    /**
     * Đổi mật khẩu người dùng
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        $user = $this->repository->find($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ];
        }

        // Kiểm tra mật khẩu hiện tại có đúng không
        if (!Hash::check($currentPassword, $user->PasswordHash)) {
            return [
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng'
            ];
        }

        // Kiểm tra mật khẩu mới không được trùng mật khẩu cũ
        if (Hash::check($newPassword, $user->PasswordHash)) {
            return [
                'success' => false,
                'message' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại'
            ];
        }

        // Cập nhật mật khẩu mới
        $data = [
            'PasswordHash' => Hash::make($newPassword),
            'UpdatedBy' => $userId
        ];

        $updatedUser = $this->repository->update($user, $data);

        return [
            'success' => true,
            'message' => 'Đổi mật khẩu thành công',
            'user' => $updatedUser
        ];
    }
}