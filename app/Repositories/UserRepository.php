<?php
namespace App\Repositories;
use App\Models\User;
class UserRepository
{
    public function all()
    {
        return User::all();
    }
    public function find($UserId)
    {
        return User::find($UserId);
    }
     public function create(array $data)
    {
        return User::create($data);
    }

    public function update(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user)
    {
        return $user->delete();
    }
   

   
}