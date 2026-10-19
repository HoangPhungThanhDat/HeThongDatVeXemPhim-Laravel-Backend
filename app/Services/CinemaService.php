<?php

namespace App\Services;

use App\Repositories\CinemaRepository;
use Illuminate\Support\Facades\Auth;

class CinemaService
{
    protected $cinemaRepository;

    public function __construct(CinemaRepository $cinemaRepository)
    {
        $this->cinemaRepository = $cinemaRepository;
    }

    public function getAll()
    {
        return $this->cinemaRepository->getAll();
    }

    public function getAllWithRelations()
    {
        return $this->cinemaRepository->getAllWithRelations();
    }

    public function getById($id)
    {
        return $this->cinemaRepository->getById($id);
    }

    public function getByIdWithRelations($id)
    {
        return $this->cinemaRepository->getByIdWithRelations($id);
    }

    public function create(array $data)
    {
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        return $this->cinemaRepository->create($data);
    }

    public function update($id, array $data)
    {
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        return $this->cinemaRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->cinemaRepository->delete($id);
    }

    public function getStats($cinemaId = null)
    {
        return $this->cinemaRepository->getStats($cinemaId);
    }

    public function getShowtimesGroupedByMovie($cinemaId, $date)
    {
        $showtimes = $this->cinemaRepository->getShowtimesByCinema($cinemaId, $date);

        if ($showtimes->isEmpty()) {
            return ['cinemaId' => $cinemaId, 'date' => $date, 'movies' => []];
        }

        $movies = $showtimes->groupBy('MovieId')->map(function ($items) {
            $movie = $items->first()->movie;
            if (!$movie) return null;

            return [
                'MovieId' => $movie->MovieId,
                'Title' => $movie->Title,
                'PosterUrl' => $movie->PosterUrl,
                'Duration' => $movie->Duration,
                'Description' => $movie->Description,
                'Directors' => $movie->directors->pluck('Name')->all(),
                'Actors' => $movie->actors->pluck('Name')->all(),
                'showtimes' => $items->map(function ($showtime) {
                    return [
                        'ShowtimeId' => $showtime->ShowtimeId,
                        'StartTime' => $showtime->StartTime->format('H:i'),
                        'EndTime' => $showtime->EndTime->format('H:i'),
                        'Room' => $showtime->room->Name,
                        'RoomType' => $showtime->room->RoomType,
                        'Price' => $showtime->Price,
                    ];
                })->values()
            ];
        })->filter()->values();

        return ['cinemaId' => $cinemaId, 'date' => $date, 'movies' => $movies];
    }

    public function getShowtimesGroupedByCity($movieId)
    {
        $showtimes = $this->cinemaRepository->getShowtimesByMovie($movieId);

        $result = [];

        foreach ($showtimes as $s) {
            $cinema = $s->room->cinema;
            $city   = $cinema->City ?? 'Unknown';
            $date   = $s->StartTime->format('Y-m-d');
            $movie  = $s->movie;

            if (!$movie) continue;

            if (!isset($result[$city])) $result[$city] = [];
            if (!isset($result[$city][$cinema->CinemaId])) {
                $result[$city][$cinema->CinemaId] = [
                    'CinemaId' => $cinema->CinemaId,
                    'Name'     => $cinema->Name,
                    'Address'  => $cinema->Address,
                    'City'     => $cinema->City,
                    'Movies'   => []
                ];
            }

            if (!isset($result[$city][$cinema->CinemaId]['Movies'][$movie->MovieId])) {
                $result[$city][$cinema->CinemaId]['Movies'][$movie->MovieId] = [
                    'MovieId'     => $movie->MovieId,
                    'Title'       => $movie->Title,
                    'PosterUrl'   => $movie->PosterUrl,
                    'Duration'    => $movie->Duration,
                    'Description' => $movie->Description,
                    'Directors'   => $movie->directors->pluck('Name')->all(),
                    'Actors'      => $movie->actors->pluck('Name')->all(),
                    'Dates'       => []
                ];
            }

            if (!isset($result[$city][$cinema->CinemaId]['Movies'][$movie->MovieId]['Dates'][$date])) {
                $result[$city][$cinema->CinemaId]['Movies'][$movie->MovieId]['Dates'][$date] = [];
            }

            $result[$city][$cinema->CinemaId]['Movies'][$movie->MovieId]['Dates'][$date][] = [
                'ShowtimeId' => $s->ShowtimeId,
                'StartTime'  => $s->StartTime->format('H:i'),
                'EndTime'    => $s->EndTime->format('H:i'),
                'Room'       => $s->room->Name,
                'RoomType'   => $s->room->RoomType,
                'Price'      => $s->Price,
            ];
        }

        foreach ($result as $city => $cinemas) {
            foreach ($cinemas as $cinemaId => $cinemaData) {
                foreach ($cinemaData['Movies'] as $movieId => $movieData) {
                    $result[$city][$cinemaId]['Movies'][$movieId]['Dates'] =
                        array_map(fn($times, $date) => [
                            'Date'      => $date,
                            'Showtimes' => $times
                        ], $movieData['Dates'], array_keys($movieData['Dates']));
                    $result[$city][$cinemaId]['Movies'][$movieId]['Movies'] = $result[$city][$cinemaId]['Movies'][$movieId]['Dates'];
                    unset($result[$city][$cinemaId]['Movies'][$movieId]['Dates']);
                }
                $result[$city][$cinemaId]['Movies'] = array_values($result[$city][$cinemaId]['Movies']);
            }
            $result[$city] = array_values($result[$city]);
        }

        return $result;
    }

    public function getMoviesByCinema($cinemaId)
    {
        return $this->cinemaRepository->getMoviesByCinema($cinemaId);
    }

    public function getDatesByCinemaAndMovie($cinemaId, $movieId)
    {
        return $this->cinemaRepository->getDatesByCinemaAndMovie($cinemaId, $movieId);
    }

    public function getShowtimesByMovieAndDate($cinemaId, $movieId, $date)
    {
        return $this->cinemaRepository->getShowtimesByMovieAndDate($cinemaId, $movieId, $date);
    }
}