<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\UserApiService;

class UserController extends Controller
{
    /**
     * Display a paginated list of users, optionally filtered by gender.
     * Fetches data from cache or external API service.
     *
     * @param  UserApiService  $apiService
     * @return \Illuminate\View\View
     */
    public function index(UserApiService $apiService)
    {
        $page = request()->get('page', 1);
        $perPage = 10;
        $gender = request()->get('gender');
        $cacheKey = "user_base_data";

        $allUsers = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($apiService) {
            return $apiService->fetchUsers(50);
        });

        if (!$allUsers) {
            $paginated = new LengthAwarePaginator([], 0, 10, $page);
            return view('users.index', [
                'users' => $paginated,
                'error' => 'Unable to fetch users at this moment. Please check API URL or Internet connection.'
            ]);
        }

        if (in_array($gender, ['male', 'female'])) {
            $allUsers = $allUsers->where('gender', $gender)->values();
        }

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $allUsers->forPage($page, $perPage),
            $allUsers->count(),
            $perPage,
            $page,
            ['path' => url('/users'), 'query' => ['gender' => $gender]]
        );

        return view('users.index', ['users' => $paginated, 'error' => null]);
    }

    /**
     * Export current paginated user data as a CSV file.
     * Optionally filters by gender from the cached dataset.
     *
     * @return StreamedResponse|RedirectResponse
     */
    public function export(): StreamedResponse|RedirectResponse
    {
        $page = request()->get('page', 1);
        $gender = request()->get('gender');
        $perPage = 10;

        $allUsers = Cache::get('user_base_data');

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
