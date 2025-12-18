<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Dashboard - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}" />
  <style>
    .dashboard-content .container-fluid {
        margin-left: 0 !important;
        padding-left: 15px !important;
        padding-right: 15px !important;
        max-width: 100% !important;
    }
    .dashboard-content .row {
        margin-left: -12px !important;
        margin-right: -12px !important;
    }
    .dashboard-content .col-xl-2,
    .dashboard-content .col-lg-3,
    .dashboard-content .col-md-4,
    .dashboard-content .col-sm-6 {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }
    .dashboard-content .section-title {
        justify-content: flex-start !important;
        text-align: left !important;
        padding-left: 0 !important;
        margin-left: 0 !important;
    }
    
    .progress-container {
        margin-top: 8px;
    }
    
    .progress-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 4px;
    }

    .genre-section {
        margin-top: 2.5rem;
    }

    /* NSFW Badge & Blur */
    .nsfw-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      z-index: 2;
      font-size: 0.6rem;
    }
    
    .nsfw-blur {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(5px);
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
      padding: 10px;
      font-size: 0.75rem;
    }

    .story-img {
      position: relative;
    }

    /* Settings Modal */
    .settings-section {
      background: white;
    }

    .section-header {
      padding: 1rem 1.5rem;
      background: #f8f9fa;
      font-weight: 600;
      color: #333;
      border-bottom: 1px solid #e9ecef;
    }

    .setting-item {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #f0f0f0;
      transition: background-color 0.2s;
    }

    .setting-item:hover {
      background-color: #f8f9fa;
    }

    .setting-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1.5rem;
    }

    .setting-info {
      flex: 1;
    }

    .setting-title {
      font-weight: 600;
      color: #333;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .setting-description {
      color: #666;
      font-size: 0.875rem;
      margin: 0;
      line-height: 1.5;
    }

    .custom-switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 28px;
    }

    .custom-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .3s;
      border-radius: 28px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 20px;
      width: 20px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .3s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    input:checked + .slider:before {
      transform: translateX(22px);
    }
  </style>
</head>
<body>
  <!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2"
                viewBox="0 0 16 16">
                <path
                    d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
            </svg>
            Storyline
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link active" href="{{ route('dashboard') }}"><i class="fas fa-th-large me-1"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('write') }}"><i class="fas fa-pen me-1"></i>Write</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('browse') }}"><i class="fas fa-compass me-1"></i>Browse</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('mystories') }}"><i class="fas fa-book me-1"></i>My Stories</a></li>
                
                <li class="nav-item dropdown ms-2">
    <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown">
        <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center" 
             style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            @if(!empty($profileImage) && $profileImage !== 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=667eea&color=fff')
                <img src="{{ $profileImage }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <span style="color: white; font-weight: bold;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            @endif
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i>My Profile</a></li>
        <li><a class="dropdown-item" href="{{ route('mystories') }}"><i class="fas fa-book me-2"></i>My Stories</a></li>
        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fas fa-cog me-2"></i>Settings</a></li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item border-0 bg-transparent">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </form>
        </li>
    </ul>
</li>
            </ul>
        </div>
    </div>
</nav>

  <div class="dashboard-content" style="padding-top: 20px;">
    <div class="container-fluid">
      <h4 class="section-title"><i class="fas fa-bookmark"></i>My Stories</h4>
      <div class="row g-3">
        @forelse ($userStories as $story)
          @php
            $isNsfw = $story['is_nsfw'] ?? false;
          @endphp
          <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 story-item" data-nsfw="{{ $isNsfw ? 'true' : 'false' }}">
            <div class="card h-100 story-card" data-story-id="{{ $story['id'] }}">
              <div style="position: relative;">
                <img src="{{ $story['cover_image'] }}" class="card-img-top story-img" alt="{{ $story['title'] }}">
                @if($isNsfw)
                  <span class="badge bg-danger nsfw-badge"><i class="fas fa-exclamation-triangle"></i> 18+</span>
                @endif
              </div>
              <div class="card-body">
                <h6 class="card-title">{{ $story['title'] }}</h6>
                <small class="text-muted">by {{ $story['author'] }}</small>
                <div class="mt-2">
                  @if (!empty($story['genre']) && is_array($story['genre']))
                    @foreach ($story['genre'] as $genre)
                      <span class="badge {{ app('App\Http\Controllers\DashboardController')->getGenreColor($genre) }} me-1 mb-1">{{ $genre }}</span>
                    @endforeach
                  @endif
                </div>
                <div class="story-stats">
                  <span class="reads">{{ app('App\Http\Controllers\DashboardController')->formatReads($story['reads']) }} reads</span>
                  <span class="rating">{{ number_format($story['rating'], 1) }} ★</span>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12">
            <div class="empty-state">
              <i class="fas fa-book-open"></i>
              <h4>No stories yet</h4>
              <p>You haven't uploaded any stories. Start your writing journey today!</p>
              <a href="{{ route('write') }}" class="btn btn-main">Write Your First Story</a>
            </div>
          </div>
        @endforelse
      </div>

      <div class="genre-section">
        <h4 class="section-title"><i class="fas fa-play-circle"></i>Continue Reading</h4>
        <div class="row g-3">
          @forelse ($continueStories as $story)
            @php
              $isNsfw = $story['is_nsfw'] ?? false;
            @endphp
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 story-item" data-nsfw="{{ $isNsfw ? 'true' : 'false' }}">
              <div class="card h-100 story-card" data-story-id="{{ $story['id'] }}">
                <div style="position: relative;">
                  <img src="{{ $story['cover_image'] }}" class="card-img-top story-img" alt="{{ $story['title'] }}">
                  @if($isNsfw)
                    <span class="badge bg-danger nsfw-badge"><i class="fas fa-exclamation-triangle"></i> 18+</span>
                  @endif
                </div>
                <div class="card-body">
                  <h6 class="card-title">{{ $story['title'] }}</h6>
                  <small class="text-muted">by {{ $story['author'] }}</small>
                  <div class="story-stats">
                    <span class="reads">{{ app('App\Http\Controllers\DashboardController')->formatReads($story['reads']) }} reads</span>
                    <span class="rating">{{ number_format($story['rating'], 1) }} ★</span>
                  </div>
                  <div class="progress-container">
                    <div class="progress" style="height: 4px;">
                      <div class="progress-bar" style="width: {{ $story['progress_percentage'] ?? 0 }}%"></div>
                    </div>
                    <div class="progress-text">Chapter {{ ($story['current_chapter_index'] ?? 0) + 1 }} • {{ $story['progress_percentage'] ?? 0 }}%</div>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="col-12">
              <div class="empty-state">
                <i class="fas fa-book-reader"></i>
                <h4>No reading progress</h4>
                <p>Start reading stories to see them here!</p>
                <a href="{{ route('browse') }}" class="btn btn-main">Browse Stories</a>
              </div>
            </div>
          @endforelse
        </div>
      </div>

      @foreach(['Horror', 'Thriller', 'Romance', 'Comedy'] as $genreName)
        @php
          $genreStories = app('App\Http\Controllers\DashboardController')->getStoriesByGenre($allStories, $genreName);
          $genreIcons = ['Horror' => 'ghost', 'Thriller' => 'user-secret', 'Romance' => 'heart', 'Comedy' => 'laugh'];
        @endphp
        <div class="genre-section">
          <h4 class="section-title"><i class="fas fa-{{ $genreIcons[$genreName] }}"></i>{{ $genreName }} Stories</h4>
          <div class="row g-3">
            @forelse (array_slice($genreStories, 0, 6) as $story)
              @php
                $isNsfw = $story['is_nsfw'] ?? false;
              @endphp
              <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 story-item" data-nsfw="{{ $isNsfw ? 'true' : 'false' }}">
                <div class="card h-100 story-card" data-story-id="{{ $story['id'] }}">
                  <div style="position: relative;">
                    <img src="{{ $story['cover_image'] }}" class="card-img-top story-img" alt="{{ $story['title'] }}">
                    @if($isNsfw)
                      <span class="badge bg-danger nsfw-badge"><i class="fas fa-exclamation-triangle"></i> 18+</span>
                    @endif
                  </div>
                  <div class="card-body">
                    <h6 class="card-title">{{ $story['title'] }}</h6>
                    <small class="text-muted">by {{ $story['author'] }}</small>
                    <div class="story-stats">
                      <span class="reads">{{ app('App\Http\Controllers\DashboardController')->formatReads($story['reads']) }} reads</span>
                      <span class="rating">{{ number_format($story['rating'], 1) }} ★</span>
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="col-12"><p class="text-muted">No {{ strtolower($genreName) }} stories available yet.</p></div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <!-- Settings Modal -->
  <div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
          <h5 class="modal-title"><i class="fas fa-cog me-2"></i>Settings</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <div class="settings-section">
            <div class="section-header"><i class="fas fa-eye me-2"></i>Content Preferences</div>
            <div class="setting-item">
              <div class="setting-content">
                <div class="setting-info">
                  <div class="setting-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    18+ Content Filter
                  </div>
                  <p class="setting-description">Show or hide NSFW stories</p>
                </div>
                <div>
                  <label class="custom-switch">
                    <input type="checkbox" id="modalShowNsfw">
                    <span class="slider"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div id="modalSaveStatus" class="flex-grow-1 text-muted">
            <i class="fas fa-info-circle me-1"></i>Changes save automatically
          </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <p class="text-white-50">&copy; 2025 Storyline. All rights reserved.</p>
        </div>
        <div class="col-md-6 text-md-end">
          <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> for storytellers</p>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Story card clicks
      document.querySelectorAll('.story-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(e) {
          if (e.target.closest('button') || e.target.closest('a')) return;
          const storyId = this.getAttribute('data-story-id');
          if (storyId) window.location.href = `/stories/${storyId}`;
        });
      });

      // Load current NSFW setting when modal opens
      const settingsModal = document.getElementById('settingsModal');
      const modalToggle = document.getElementById('modalShowNsfw');
      
      if (settingsModal && modalToggle) {
        settingsModal.addEventListener('show.bs.modal', async function () {
          console.log('Settings modal opening...');
          // Fetch current setting from server
          try {
            const response = await fetch('{{ route("settings.get") }}', {
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              }
            });
            const data = await response.json();
            console.log('Settings loaded:', data);
            
            if (data.success && data.settings) {
              console.log('show_nsfw value:', data.settings.show_nsfw);
              console.log('show_nsfw type:', typeof data.settings.show_nsfw);
              
              // Convert 1/0 to boolean properly
              modalToggle.checked = !!data.settings.show_nsfw;
              console.log('Toggle set to:', modalToggle.checked);
            }
          } catch (error) {
            console.error('Error loading settings:', error);
          }
        });

        // Settings modal toggle save
        modalToggle.addEventListener('change', function() {
          const checked = this.checked;
          const statusEl = document.getElementById('modalSaveStatus');
          
          statusEl.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
          statusEl.className = 'flex-grow-1 text-warning';
          
          fetch('{{ route("settings.update") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ show_nsfw: checked })
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              statusEl.innerHTML = '<i class="fas fa-check-circle me-1"></i> Saved! Reloading...';
              statusEl.className = 'flex-grow-1 text-success';
              setTimeout(() => window.location.reload(), 1000);
            } else {
              throw new Error('Failed');
            }
          })
          .catch(err => {
            statusEl.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Error saving';
            statusEl.className = 'flex-grow-1 text-danger';
          });
        });
      }
    });
  </script>
</body>
</html>