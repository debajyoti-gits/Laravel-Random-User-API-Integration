<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class UserApiService
{
    /**
     * Fetch a collection of user data from the external API.
     *
     * @param int $count Number of users to retrieve.
     * @return Collection|null Returns a collection of users or null on failure.
     */
    public function fetchUsers(int $count = 50): ? Collection
    {
        try {
            $response = Http::timeout(5)->get('https://randomuser.me/api/', [
                'results' => $count,
            ]);

            if (!$response->successful()) {
                return null;
            }

            return collect($response->json('results'))->map(function ($user) {
                return [
                    'name' => $user['name']['first'] . ' ' . $user['name']['last'],
                    'email' => $user['email'],
                    'gender' => $user['gender'],
                    'nationality' => $user['nat'],
                ];
            });

        } catch (\Exception $e) {
            return null;
        }
    }
}
