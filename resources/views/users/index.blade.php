@php 
use Illuminate\Pagination\Paginator; 
Paginator::useBootstrap(); 
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h2>User List</h2>

        @if($error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endif
        <form method="GET" action="/users" class="mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select name="gender" class="form-select" onchange="this.form.submit()">
                        <option value="">All Genders</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
            </div>
        </form>
        <div class="mb-3">
            <a href="{{ url('/users/export?' . http_build_query(request()->query())) }}" class="btn btn-sm btn-success">
                Export Current Page to CSV
            </a>
        </div>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Gender</th><th>Nationality</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user['name'] }}</td>
                        <td>{{ $user['email'] }}</td>
                        <td>{{ ucfirst($user['gender']) }}</td>
                        <td>{{ $user['nationality'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No users available.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div>
            {{ $users->links() }}
        </div>
    </div>
</body>
</html>
