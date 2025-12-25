<?php
namespace App\Repositories;
use App\Models\Moviecast;
class MoviecastRepository
{
    public function all()
    {
        return Moviecast::all();
    }
    public function find($CastId)
    {
        return Moviecast::find($CastId);
    }
     public function create(array $data)
    {
        return Moviecast::create($data);
    }

    public function update(Moviecast $moviecast, array $data)
    {
        $moviecast->update($data);
        return $moviecast;
    }

    public function delete(Moviecast $moviecast)
    {
        return $moviecast->delete();
    }
   

   
}