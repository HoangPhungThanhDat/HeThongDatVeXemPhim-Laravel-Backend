<?php
namespace App\Repositories;
use App\Models\Membership;
class MembershipRepository
{
    public function all()
    {
        return Membership::all();
    }
    public function find($MembershipId)
    {
        return Membership::find($MembershipId);
    }
     public function create(array $data)
    {
        return Membership::create($data);
    }

    public function update(Membership $membership, array $data)
    {
        $membership->update($data);
        return $membership;
    }

    public function delete(Membership $membership)
    {
        return $membership->delete();
    }
   

   
}