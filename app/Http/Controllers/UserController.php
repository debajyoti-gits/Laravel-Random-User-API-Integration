<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function index()
    {
        $page = request()->get('page', 1);
        $perPage = 10;
        $gender = request()->get('gender');

        $baseCacheKey = 'users_base_data_50';

        $allUsers = Cache::remember($baseCacheKey, now()->addMinutes(10), function () {
            try {
                $response = Http::timeout(5)->get('https://randomuser.me/api/', [
                    'results' => 50,
                ]);

                if (!$response->successful()) {
                    return collect();
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
                return collect();
            }
        });

        if (in_array($gender, ['male', 'female'])) {
            $filteredUsers = $allUsers->where('gender', $gender)->values();
        } else {
            $filteredUsers = $allUsers;
        }

        $paginatedUsers = new LengthAwarePaginator(
            $filteredUsers->forPage($page, $perPage),
            $filteredUsers->count(),
            $perPage,
            $page,
            ['path' => url('/users'), 'query' => ['gender' => $gender]]
        );

        return view('users.index', [
            'users' => $paginatedUsers,
            'error' => $filteredUsers->isEmpty() ? 'Unable to fetch users. Please check your internet connection or API url and try again.' : null
        ]);
    }

    public function export(): StreamedResponse|RedirectResponse
    {
        $page = request()->get('page', 1);
        $gender = request()->get('gender');
        $perPage = 10;

        $allUsers = Cache::get('users_base_data_50');

        if (!$allUsers || count($allUsers) === 0) {
            return redirect('/users')->with('error', 'No data to export.');
        }

        if (in_array($gender, ['male', 'female'])) {
            $filteredUsers = collect($allUsers)->where('gender', $gender)->values();
        } else {
            $filteredUsers = collect($allUsers);
        }

        $pageUsers = $filteredUsers->forPage($page, $perPage);

        $filename = "users_page_{$page}_" . ($gender ?: 'all') . ".csv";

        return response()->streamDownload(function () use ($pageUsers) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Name', 'Email', 'Gender', 'Nationality']);

            foreach ($pageUsers as $user) {
                fputcsv($handle, [$user['name'], $user['email'], ucfirst($user['gender']), $user['nationality']]);
            }

            fclose($handle);

        }, $filename);
    }
}
