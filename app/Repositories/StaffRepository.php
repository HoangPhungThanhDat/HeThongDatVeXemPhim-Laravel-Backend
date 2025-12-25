<?php
namespace App\Repositories;
use App\Models\Staff;
class StaffRepository
{
    public function all()
    {
        return Staff::all();
    }
    public function find($StaffId)
    {
        return Staff::find($StaffId);
    }
     public function create(array $data)
    {
        return Staff::create($data);
    }

    public function update(Staff $staff, array $data)
    {
        $staff->update($data);
        return $staff;
    }

    public function delete(Staff $staff)
    {
        return $staff->delete();
    }
   

   
}