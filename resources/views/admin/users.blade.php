<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Storyline - Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background-color: #eef1f6;
    }
    .sidebar {
      min-height: 100vh;
      background-color: #0d1321;
      color: #fff;
    }
    .sidebar a {
      color: #adb5bd;
      text-decoration: none;
      display: block;
      padding: .75rem 1rem;
      border-radius: .375rem;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #1c2333;
      color: #fff;
    }
    .top-card, .list-card {
      background: #ffffff;
      border-radius: .75rem;
      box-shadow: 0 4px 12px rgba(0,0,0,.08);
      padding: 1.5rem;
    }
    .user-avatar {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
    }
    .badge-admin {
      background-color: #dc3545;
    }
    .badge-user {
      background-color: #6c757d;
    }
    .table-hover tbody tr:hover {
      background-color: rgba(0,0,0,.02);
    }
    .nav-link.active {
      background-color: #667eea;
      color: white !important;
    }
    .points {
      font-size: 1.5rem;
      font-weight: bold;
      color: #0d1321; 
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
      <h4 class="text-white">
        <i class="fas fa-book me-2"></i>Storyline
      </h4>
      <small class="text-muted d-block mb-4">Admin Panel</small>
      <ul class="nav flex-column mt-4">
        <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-th-large me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a href="{{ route('admin.users') }}" class="nav-link active"><i class="fas fa-users me-2"></i>Manage Users</a></li>
        <li class="nav-item"><a href="{{ route('admin.stories') }}" class="nav-link"><i class="fas fa-book me-2"></i>Manage Stories</a></li>
        <li class="nav-item"><a href="{{ route('admin.comments') }}" class="nav-link"><i class="fas fa-comments me-2"></i>Comments</a></li>
        <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-bell me-2"></i>Notifications</a></li>
        <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-cog me-2"></i>Settings</a></li>
        <li class="nav-item">
          <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="nav-link border-0 bg-transparent text-start w-100" style="color: #adb5bd;">
              <i class="fas fa-sign-out-alt me-2"></i>Log Out
            </button>
          </form>
        </li>
      </ul>
      <div class="mt-auto pt-3">
        <button type="button" class="btn btn-outline-light w-100">
          <strong>{{ auth()->user()->name ?? 'Admin' }}</strong><br>
          <small>{{ auth()->user()->email ?? 'Admin Email' }}</small>
        </button>
      </div>
    </nav>

    <!-- Main content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Users</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="fas fa-plus me-2"></i>Add New User
        </button>
      </div>

      <!-- Messages -->
      @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif
      
      @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <!-- User Statistics -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="top-card">
            <h5>Total Users</h5>
            <div class="points">{{ $totalUsers }}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="top-card">
            <h5>Administrators</h5>
            <div class="points">{{ count($adminUsers) }}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="top-card">
            <h5>Regular Users</h5>
            <div class="points">{{ count($regularUsers) }}</div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="list-card">
        <h5 class="mb-4">All Users</h5>
        
        @if (!empty($users))
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($users as $user)
                  @php
                    $isCurrentUser = $user['id'] == auth()->user()->supabase_id;
                  @endphp
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">
                          {{ strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                          <strong>{{ $user['first_name'] ?? '' }} {{ $user['last_name'] ?? '' }}</strong>
                        </div>
                      </div>
                    </td>
                    <td>{{ $user['email'] ?? '' }}</td>
                    <td>
                      <form method="POST" action="{{ route('admin.users.updateRole') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" 
                                {{ $isCurrentUser ? 'disabled' : '' }}>
                          <option value="user" {{ ($user['role'] ?? 'user') === 'user' ? 'selected' : '' }}>User</option>
                          <option value="admin" {{ ($user['role'] ?? 'user') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                      </form>
                    </td>
                    <td>
                      @php
                        $createdAt = $user['created_at'] ?? now()->toISOString();
                        $date = new DateTime($createdAt);
                      @endphp
                      {{ $date->format('M j, Y') }}
                    </td>
                    <td>
                      @if (!$isCurrentUser)
                        <form method="POST" action="{{ route('admin.users.delete') }}" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                          @csrf
                          <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                          <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      @else
                        <span class="text-muted">Current User</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted">No users found in the database.</p>
          </div>
        @endif
      </div>
    </main>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.users.add') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
            <div class="form-text">Password must be at least 8 characters long.</div>
          </div>
          
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role">
              <option value="user">User</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Auto-dismiss alerts after 5 seconds
  setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);

  document.addEventListener('DOMContentLoaded', function() {
    // Clear any cached data
    if (window.performance && window.performance.navigation && window.performance.navigation.type === 2) {
        // Page was loaded from cache (back/forward button)
        window.location.reload();
    }
    
    // Disable browser caching for admin pages
    if (window.location.pathname.includes('/admin')) {
        // Add a unique parameter to prevent caching
        if (window.history && window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.set('_t', Date.now());
            window.history.replaceState({}, '', url);
        }
        
        // Reload page if it was restored from cache
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    }
});

// Clear storage on logout
function clearBrowserStorage() {
    localStorage.clear();
    sessionStorage.clear();
    
    // Clear indexedDB if exists
    if (window.indexedDB) {
        window.indexedDB.databases().then(function(databases) {
            databases.forEach(function(db) {
                window.indexedDB.deleteDatabase(db.name);
            });
        });
    }
}

// Attach to logout forms
document.addEventListener('submit', function(e) {
    if (e.target && e.target.action && e.target.action.includes('logout')) {
        clearBrowserStorage();
    }
});
</script>
</body>
</html>