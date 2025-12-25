<?php
namespace App\Repositories;
use App\Models\Showtime;
class ShowtimeRepository
{
    public function all()
    {
        return Showtime::all();
    }
    public function find($ShowtimeId)
    {
        return Showtime::find($ShowtimeId);
    }
     public function create(array $data)
    {
        return Showtime::create($data);
    }

    public function update(Showtime $showtime, array $data)
    {
        $showtime->update($data);
        return $showtime;
    }

    public function delete(Showtime $showtime)
    {
        return $showtime->delete();
    }
   

   
}