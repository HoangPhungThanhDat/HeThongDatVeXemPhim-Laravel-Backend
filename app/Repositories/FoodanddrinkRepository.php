<?php
namespace App\Repositories;
use App\Models\Foodanddrink;
class FoodanddrinkRepository
{
    public function all()
    {
        return Foodanddrink::all();
    }
    public function find($ItemId)
    {
        return Foodanddrink::find($ItemId);
    }
     public function create(array $data)
    {
        return Foodanddrink::create($data);
    }

    public function update(Foodanddrink $foodanddrink, array $data)
    {
        $foodanddrink->update($data);
        return $foodanddrink;
    }

    public function delete(Foodanddrink $foodanddrink)
    {
        return $foodanddrink->delete();
    }
   

   
}