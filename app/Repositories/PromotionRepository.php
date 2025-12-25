<?php
namespace App\Repositories;
use App\Models\Promotion;
class PromotionRepository
{
    public function all()
    {
        return Promotion::all();
    }
    public function find($PromotionId)
    {
        return Promotion::find($PromotionId);
    }
     public function create(array $data)
    {
        return Promotion::create($data);
    }

    public function update(Promotion $promotion, array $data)
    {
        $promotion->update($data);
        return $promotion;
    }

    public function delete(Promotion $promotion)
    {
        return $promotion->delete();
    }
   

   
}