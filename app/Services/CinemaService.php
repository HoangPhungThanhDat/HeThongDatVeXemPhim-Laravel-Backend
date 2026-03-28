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

    public function getById($id)
    {
        return $this->cinemaRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->cinemaRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->cinemaRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->cinemaRepository->delete($id);
    }
    // lich-chieu-phim-ngay-15-3-2026
      public function getShowtimes($cinemaId, $date)
    {
        return $this->cinemaRepository->getShowtimesByCinema($cinemaId, $date);
    }
     /**
 * Lấy danh sách rạp chiếu phim + suất chiếu trong ngày
 */
// CinemaService.php
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

        // thêm ngày và showtime
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

    // chuyển Dates từ assoc → mảng để frontend dễ duyệt
    foreach ($result as $city => $cinemas) {
        foreach ($cinemas as $cinemaId => $cinemaData) {
            foreach ($cinemaData['Movies'] as $movieId => $movieData) {
                $result[$city][$cinemaId]['Movies'][$movieId]['Dates'] =
                    array_map(fn($times, $date) => [
                        'Date'      => $date,
                        'Showtimes' => $times
                    ], $movieData['Dates'], array_keys($movieData['Dates']));
                // reset key để là array, không phải associative
                $result[$city][$cinemaId]['Movies'][$movieId]['Movies'] = $result[$city][$cinemaId]['Movies'][$movieId]['Dates'];
                unset($result[$city][$cinemaId]['Movies'][$movieId]['Dates']);
            }
            // convert Movies từ assoc → array
            $result[$city][$cinemaId]['Movies'] = array_values($result[$city][$cinemaId]['Movies']);
        }
        // convert Cinema từ assoc → array
        $result[$city] = array_values($result[$city]);
    }

    return $result;
}
}

