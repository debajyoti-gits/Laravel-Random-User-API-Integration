<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{

    public function index() {
        $page = request()->get('page', 1);
        $perPage = 10;
        $gender = request()->get('gender');
        $cacheKey = "users_page_{$page}_gender_" . ($gender ?: 'all');

        $users = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($perPage, $page, $gender) {

            try {
                $response = Http::timeout(5)->get('https://randomuser.me/api/', [
                    'results' => 50,
                ]);
        
                if (!$response->successful()) {
                    return null;
                }
        
                $allUsers = collect($response->json('results'))->map(function ($user) {
                    return [
                        'name' => $user['name']['first'] . ' ' . $user['name']['last'],
                        'email' => $user['email'],
                        'gender' => $user['gender'],
                        'nationality' => $user['nat'],
                    ];
                });
        
                if (in_array($gender, ['male', 'female'])) {
                    $allUsers = $allUsers->where('gender', $gender)->values();
                }
        
                return new LengthAwarePaginator(
                    $allUsers->forPage($page, $perPage),
                    $allUsers->count(),
                    $perPage,
                    $page,
                    ['path' => url('/users'), 'query' => ['gender' => $gender]]
                );
        
            } catch (\Exception $e) {
                return null;
            }
        });

        if (!$users || !$users instanceof LengthAwarePaginator) {
            $users = new LengthAwarePaginator([], 0, 10, request()->get('page', 1));
            return view('users.index', ['users' => $users, 'error' => 'Unable to fetch users. Please check your internet connection or API url and try again.']);
        }

        return view('users.index', ['users' => $users, 'error' => null]);
    }

    public function export(): StreamedResponse {
        $page = request()->get('page', 1);
        $gender = request()->get('gender');
        $cacheKey = "users_page_{$page}_gender_" . ($gender ?: 'all');

        $users = Cache::get($cacheKey);

        if (!$users || count($users) === 0) {
            return redirect('/users')->with('error', 'No data to export.');
        }

        $filename = "users_page_{$page}_" . ($gender ?: 'all') . ".csv";

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Name', 'Email', 'Gender', 'Nationality']);

            foreach ($users as $user) {
                fputcsv($handle, [$user['name'], $user['email'], ucfirst($user['gender']), $user['nationality']]);
            }

            fclose($handle);
        }, $filename);
    }
}
