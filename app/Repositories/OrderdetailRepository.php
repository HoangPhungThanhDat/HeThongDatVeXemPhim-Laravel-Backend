<?php
namespace App\Repositories;
use App\Models\Orderdetail;
class OrderdetailRepository
{
    public function all()
    {
        return Orderdetail::all();
    }
    public function find($OrderDetailId)
    {
        return Orderdetail::find($OrderDetailId);
    }
     public function create(array $data)
    {
        return Orderdetail::create($data);
    }

    public function update(Orderdetail $orderdetail, array $data)
    {
        $orderdetail->update($data);
        return $orderdetail;
    }

    public function delete(Orderdetail $orderdetail)
    {
        return $orderdetail->delete();
    }
   

   
}