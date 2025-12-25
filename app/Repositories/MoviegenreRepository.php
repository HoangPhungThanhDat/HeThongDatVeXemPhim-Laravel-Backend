<?php
namespace App\Repositories;
use App\Models\Moviegenre;
class MoviegenreRepository
{
    public function all()
    {
        return Moviegenre::all();
    }
    public function find($MovieGenreId)
    {
        return Moviegenre::find($MovieGenreId);
    }
     public function create(array $data)
    {
        return Moviegenre::create($data);
    }

    public function update(Moviegenre $moviegenre, array $data)
    {
        $moviegenre->update($data);
        return $moviegenre;
    }

    public function delete(Moviegenre $moviegenre)
    {
        return $moviegenre->delete();
    }
   

   
}