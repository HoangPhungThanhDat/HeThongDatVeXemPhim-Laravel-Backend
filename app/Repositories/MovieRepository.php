<?php
namespace App\Repositories;
use App\Models\Movie;
class MovieRepository
{
    public function all()
    {
        return Movie::all();
    }
    public function find($MovieId)
    {
        return Movie::find($MovieId);
    }
     public function create(array $data)
    {
        return Movie::create($data);
    }

    public function update(Movie $movie, array $data)
    {
        $movie->update($data);
        return $movie;
    }

    public function delete(Movie $movie)
    {
        return $movie->delete();
    }
   

   
}