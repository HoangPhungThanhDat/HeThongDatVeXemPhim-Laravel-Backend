<?php
namespace App\Repositories;
use App\Models\Review;
class ReviewRepository
{
    public function all()
    {
        return Review::all();
    }
    public function find($ReviewId)
    {
        return Review::find($ReviewId);
    }
     public function create(array $data)
    {
        return Review::create($data);
    }

    public function update(Review $review, array $data)
    {
        $review->update($data);
        return $review;
    }

    public function delete(Review $review)
    {
        return $review->delete();
    }
   

   
}