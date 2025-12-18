<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Storyline - Admin Dashboard</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Font Awesome -->
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
    .top-card, .list-card, .chart-card {
      background: #ffffff;
      border-radius: .75rem;
      box-shadow: 0 4px 12px rgba(0,0,0,.08);
      padding: 1.5rem;
    }
    .points {
      font-size: 1.5rem;
      font-weight: bold;
      color: #0d1321; 
    }
    .progress {
      background-color: #e6e9f0; 
      border-radius: 10px;
      overflow: hidden;
    }
    .progress-bar {
      border-radius: 10px;
    }
    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
    }
    .stat-card {
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-2px);
    }
    .nav-link.active {
      background-color: #667eea;
      color: white !important;
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
        <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-th-large me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a href="{{ route('admin.users') }}" class="nav-link"><i class="fas fa-users me-2"></i>Manage Users</a></li>
        <li class="nav-item"><a href="{{ route('admin.stories') }}" class="nav-link"><i class="fas fa-book me-2"></i>Manage Stories</a></li>
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
        <button type="button" class="btn btn-outline-light w-100" data-bs-toggle="modal" data-bs-target="#userProfileModal">
          <strong id="sidebarUserName">{{ auth()->user()->name ?? 'Admin' }}</strong><br>
          <small id="sidebarUserEmail">{{ auth()->user()->email ?? 'Admin Email' }}</small>
        </button>
      </div>
    </nav>

    <!-- Main content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
        <div class="d-flex align-items-center">
          <button class="btn btn-sm btn-outline-primary me-2" onclick="refreshStats()">
            <i class="fas fa-sync-alt"></i> Refresh
          </button>
          <small class="text-muted">Last updated: {{ now()->format('F j, Y g:i A') }}</small>
        </div>
      </div>

      <!-- Top stats - REMOVED Pending Stories and Total Reads -->
      <div class="row g-3 mb-4" id="statsContainer">
        <div class="col-md-6">
          <div class="top-card stat-card">
            <h5><i class="fas fa-users text-primary me-2"></i>Total Users</h5>
            <div class="points">{{ $totalUsers }}</div>
            <small class="text-muted">Registered users</small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="top-card stat-card">
            <h5><i class="fas fa-book text-success me-2"></i>Total Stories</h5>
            <div class="points">{{ $totalStories }}</div>
            <small class="text-muted">Published stories</small>
          </div>
        </div>
      </div>

      <!-- Charts Row - REMOVED Top Stories by Reads Chart -->
      <div class="row mb-4">
        <!-- Genre Distribution Chart Only -->
        <div class="col-md-12">
          <div class="chart-card">
            <h5 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Stories by Genre</h5>
            <div class="chart-container">
              <canvas id="genreChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Genre Statistics with Progress Bars -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="list-card">
            <h5 class="mb-4"><i class="fas fa-list me-2"></i>Genre Breakdown</h5>
            @if (!empty($genreStats))
              @php
                $maxGenreCount = max($genreStats);
              @endphp
              @foreach ($genreStats as $genre => $count)
                @php
                  $percentage = $totalStories > 0 ? ($count / $totalStories) * 100 : 0;
                  $progressWidth = $maxGenreCount > 0 ? ($count / $maxGenreCount) * 100 : 0;
                @endphp
                <div class="mb-3">
                  <div class="d-flex justify-content-between">
                    <span class="fw-medium">{{ $genre }}</span>
                    <span class="fw-bold">{{ $count }} stories</span>
                  </div>
                  <div class="progress" style="height: 18px;">
                    <div class="progress-bar" 
                         role="progressbar" 
                         style="width: {{ $progressWidth }}%; background-color: {{ app('App\Http\Controllers\AdminController')->getGenreColor($genre) }}"
                         aria-valuenow="{{ $count }}" 
                         aria-valuemin="0" 
                         aria-valuemax="{{ $maxGenreCount }}">
                    </div>
                  </div>
                  <small class="text-muted">{{ number_format($percentage, 1) }}% of total stories</small>
                </div>
              @endforeach
              
              <div class="mt-4 text-end">
                <span class="fw-bold">Total Stories: {{ $totalStories }}</span>
              </div>
            @else
              <div class="text-center text-muted py-4">
                <i class="fas fa-book fa-2x mb-2"></i>
                <p>No stories found in the database.</p>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="row">
        <div class="col-md-8">
          <div class="chart-card">
            <h5 class="mb-4"><i class="fas fa-history me-2"></i>Recent Stories</h5>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Reads</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($recentStories as $story)
                    @php
                      $genres = app('App\Http\Controllers\AdminController')->extractGenres($story);
                    @endphp
                    <tr>
                      <td>
                        {{ Str::limit($story['title'], 20) }}
                        @if($story['is_nsfw'])
                          <span class="badge bg-danger ms-1">18+</span>
                        @endif
                      </td>
                      <td>{{ Str::limit($story['author'], 15) }}</td>
                      <td>
                        @if(!empty($genres))
                          @foreach(array_slice($genres, 0, 2) as $genre)
                            <span class="badge bg-primary">{{ $genre }}</span>
                          @endforeach
                        @endif
                      </td>
                      <td>{{ $story['reads'] }}</td>
                      <td>
                        @if($story['reads'] == 0)
                          <span class="badge bg-warning">New</span>
                        @else
                          <span class="badge bg-success">Active</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-muted">No recent stories</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="chart-card h-100">
            <h5 class="mb-4"><i class="fas fa-chart-line me-2"></i>User Growth</h5>
            <div class="d-flex align-items-center justify-content-center" style="height: 200px;">
              <div class="text-center">
                <h1 class="display-4 text-primary">{{ $userGrowth['recent'] }}</h1>
                <p class="text-muted">New users in last 30 days</p>
                @if($userGrowth['growth'] > 0)
                  <span class="badge bg-success">
                    <i class="fas fa-arrow-up"></i> {{ $userGrowth['growth'] }}% growth
                  </span>
                @elseif($userGrowth['growth'] < 0)
                  <span class="badge bg-danger">
                    <i class="fas fa-arrow-down"></i> {{ abs($userGrowth['growth']) }}% decline
                  </span>
                @else
                  <span class="badge bg-secondary">No growth</span>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Genre Chart Data
    const genreData = {
      labels: @json($genreLabels),
      datasets: [{
        data: @json($genreCounts),
        backgroundColor: [
          '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
          '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
          '#36A2EB', '#FFCE56'
        ],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    };

    // Initialize Genre Pie Chart
    const genreCtx = document.getElementById('genreChart').getContext('2d');
    new Chart(genreCtx, {
      type: 'pie',
      data: genreData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `${label}: ${value} stories (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  });

  function refreshStats() {
    fetch('{{ route("admin.stats") }}')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const stats = data.stats;
          document.querySelectorAll('.top-card .points').forEach((el, index) => {
            if (index === 0) el.textContent = stats.totalUsers;
            if (index === 1) el.textContent = stats.totalStories;
          });
          
          // Update timestamp
          document.querySelector('small.text-muted').textContent = 
            'Last updated: ' + new Date().toLocaleString('en-US', {
              month: 'long',
              day: 'numeric',
              year: 'numeric',
              hour: 'numeric',
              minute: '2-digit',
              hour12: true
            });
        }
      })
      .catch(error => {
        console.error('Error refreshing stats:', error);
      });
  }

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