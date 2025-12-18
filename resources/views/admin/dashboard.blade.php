<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Storyline - Admin Dashboard</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background-color: #f8f9fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow-x: hidden;
      margin: 0;
      padding: 0;
    }

    /* Sidebar - Fixed and Non-Scrollable */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: 250px;
      background-color: #0d1321;
      color: #fff;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      padding: 1rem;
      overflow-y: auto;
      transition: transform 0.3s ease;
    }

    .sidebar::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
      background: #0d1321;
    }

    .sidebar::-webkit-scrollbar-thumb {
      background: #1c2333;
      border-radius: 3px;
    }

    .sidebar a {
      color: #adb5bd;
      text-decoration: none;
      display: block;
      padding: .75rem 1rem;
      border-radius: .375rem;
      transition: all 0.3s ease;
      white-space: nowrap;
    }

    .sidebar a:hover, 
    .sidebar a.active {
      background-color: #1c2333;
      color: #fff;
    }

    .nav-link.active {
      background-color: #667eea !important;
      color: white !important;
    }

    .sidebar h4 {
      margin-bottom: 0;
    }

    .sidebar .nav {
      flex: 1;
      overflow-y: auto;
    }

    .sidebar .mt-auto {
      margin-top: auto !important;
    }

    /* Main Content Area */
    main {
      margin-left: 250px;
      width: calc(100% - 250px);
      min-height: 100vh;
      padding: 1.5rem;
    }

    /* Mobile Menu Toggle */
    .mobile-menu-toggle {
      display: none;
      position: fixed;
      top: 1rem;
      left: 1rem;
      z-index: 1001;
      background: #0d1321;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 0.375rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 999;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.show {
        transform: translateX(0);
      }
      
      main {
        margin-left: 0;
        width: 100%;
        padding-top: 4rem;
      }
      
      .mobile-menu-toggle {
        display: block !important;
      }
      
      .sidebar-overlay.show {
        display: block !important;
      }
    }

    /* Tablet Responsive */
    @media (min-width: 769px) and (max-width: 1024px) {
      .sidebar {
        width: 200px;
      }
      
      main {
        margin-left: 200px;
        width: calc(100% - 200px);
      }
      
      .sidebar a {
        font-size: 0.9rem;
        padding: 0.6rem 0.8rem;
      }
    }

    .top-card {
      background: #ffffff;
      border-radius: .75rem;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      padding: 1.25rem;
    }

    .stats-number {
      font-size: 1.1rem;
      font-weight: bold;
    }

    /* Card Styles */
    .story-card {
      height: 100%;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: none;
      border-radius: 12px;
      overflow: hidden;
      cursor: pointer;
      position: relative;
    }

    .story-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }

    .card-img-container {
      height: 180px;
      overflow: hidden;
      position: relative;
    }

    .card-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .story-card:hover .card-img {
      transform: scale(1.05);
    }

    .card-body {
      padding: 1rem;
    }

    .story-title {
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      height: 2.5rem;
    }

    .story-author {
      font-size: 0.8rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
    }

    .story-genres {
      margin-bottom: 0.75rem;
    }

    .genre-badge {
      font-size: 0.7rem;
      padding: 0.2rem 0.4rem;
      margin-right: 0.25rem;
      margin-bottom: 0.25rem;
    }

    .story-stats {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.8rem;
      color: #6c757d;
      border-top: 1px solid #eee;
      padding-top: 0.5rem;
      margin-top: 0.5rem;
    }

    .story-reads {
      color: #0d6efd;
      font-weight: 500;
    }

    .story-chapters {
      color: #28a745;
      font-weight: 500;
    }

    .card-actions {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 2;
      display: flex;
      gap: 5px;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .story-card:hover .card-actions {
      opacity: 1;
    }

    .action-btn {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      background: white;
      border: 1px solid #ddd;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      cursor: pointer;
    }

    .action-btn:hover {
      background: #f8f9fa;
    }

    .nsfw-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      z-index: 2;
      font-size: 0.6rem;
      padding: 0.25rem 0.5rem;
    }

    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: #6c757d;
    }

    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: #dee2e6;
    }

    .stories-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1.5rem;
    }

    @media (max-width: 768px) {
      main {
        padding: 1rem;
      }
      
      .stories-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
      }
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