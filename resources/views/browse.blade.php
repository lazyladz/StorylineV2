<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Browse Stories - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <style>
    /* Sticky Footer Setup */
    html, body {
      height: 100%;
      margin: 0;
    }
    
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: #eef1f6;
    }
    
    .content-wrapper {
      flex: 1 0 auto;
      padding-top: 80px; /* For fixed navbar */
      padding-bottom: 60px; /* Space before footer */
    }
    
    footer {
      flex-shrink: 0;
      background-color: #0d1321;
      color: white;
      padding: 2rem 0;
      margin-top: 40px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Story Card Styles */
    .nsfw-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      z-index: 2;
      font-size: 0.7rem;
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
      padding: 20px;
    }
    
    .story-img-container {
      position: relative;
      overflow: hidden;
      height: 250px;
    }
    
    .story-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    
    .story-card:hover .story-img {
      transform: scale(1.05);
    }
    
    /* Settings Modal Styles */
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
    
    input:disabled + .slider {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    /* Story filtering animations */
    .story-item {
      transition: all 0.3s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    #noResultsMessage {
      animation: fadeIn 0.5s ease;
      margin-bottom: 40px;
    }
    
    /* Footer specific styles */
    footer .container {
      padding-top: 20px;
      padding-bottom: 20px;
    }
    
    footer .text-white-50 {
      color: rgba(255, 255, 255, 0.7) !important;
      margin-bottom: 0.25rem;
    }
    
    footer .mb-0 {
      margin-bottom: 0.25rem;
    }
    
    /* Extra spacing for when there are few stories */
    #storiesContainer {
      min-height: 300px;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="{{ route('home') }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2" viewBox="0 0 16 16">
          <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
        </svg>
        Storyline
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          @auth
            <!-- Navigation items for logged-in users ONLY -->
            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-th-large me-1"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('write') }}"><i class="fas fa-pen me-1"></i>Write</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('mystories') }}"><i class="fas fa-book me-1"></i>My Stories</a></li>
            <li class="nav-item"><a class="nav-link active" href="{{ route('browse') }}"><i class="fas fa-search me-1"></i>Browse</a></li>
            
            <li class="nav-item dropdown ms-2">
              <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown">
                <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold;">
                  <span>{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>
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
          @else
            <!-- Navigation items for guests (non-logged in users) ONLY -->
            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#features">Features</a></li>
            <li class="nav-item"><a class="nav-link active" href="{{ route('browse') }}">Browse</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#testimonials">Testimonials</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>Login</a></li>
            <li class="nav-item"><a class="btn btn-main ms-2" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i>Sign Up</a></li>
          @endauth
        </ul>
      </div>
    </div>
  </nav>

  <!-- Content Wrapper - This will expand to push footer down -->
  <div class="content-wrapper">
    <div class="container">
      <h2 class="mb-3">Browse Stories</h2>
      <p class="text-muted mb-4">Discover adventures, mysteries, and community tales</p>
      
      @auth
        @php
          $currentUser = Auth::user();
          \Log::info('Browse page user:', ['user' => $currentUser]);
        @endphp
        <div class="alert alert-info">
          Welcome back, {{ $currentUser->first_name ?? ($currentUser->name ?? 'User') }}! Explore stories from our community.
        </div>
      @else
        <div class="alert alert-warning">
          You're browsing as a guest. You can read all stories but will need to <a href="{{ route('register') }}" class="alert-link">sign up</a> to comment, rate, or save your progress!
        </div>
      @endauth

      <div class="row mb-4">
        <div class="col-md-8">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search by title or author..." id="searchInput">
            <button class="btn btn-main" type="button" id="searchButton">
              <i class="fas fa-search"></i> Search
            </button>
          </div>
        </div>
        <div class="col-md-4">
          <select class="form-select" id="genreFilter">
            <option value="">All Genres</option>
            @foreach($popularGenres as $genre)
              <option value="{{ $genre }}">{{ $genre }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="row g-4" id="storiesContainer">
        @forelse($allStories as $story)
          @php
            $isNsfw = $story['is_nsfw'] ?? false;
            $showNsfw = auth()->check() ? ($user_settings['show_nsfw'] ?? false) : false;
            $hideNsfw = $isNsfw && !$showNsfw;
          @endphp
          
          <div class="col-xl-3 col-lg-4 col-md-6 story-item" 
               data-story-id="{{ $story['id'] }}"
               data-title="{{ strtolower($story['title']) }}"
               data-author="{{ strtolower($story['author']) }}"
               data-genre="{{ json_encode($story['genre'] ?? []) }}"
               data-nsfw="{{ $isNsfw ? 'true' : 'false' }}">
            <div class="card h-100 story-card">
              <div class="story-img-container">
                <img src="{{ $story['cover_image'] }}" class="story-img" alt="{{ $story['title'] }}" style="{{ $hideNsfw ? 'filter: blur(10px);' : '' }}">
                
                @if($isNsfw)
                  <span class="badge bg-danger nsfw-badge"><i class="fas fa-exclamation-triangle me-1"></i>18+</span>
                @endif
                
                @if($hideNsfw)
                  <div class="nsfw-blur">
                    <div>
                      <i class="fas fa-eye-slash fa-2x mb-2"></i>
                      <h6 class="mb-1">18+ Content</h6>
                      <small>Enable in settings to view</small>
                      <br>
                      <button class="btn btn-sm btn-outline-light mt-2" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="fas fa-cog me-1"></i>Open Settings
                      </button>
                    </div>
                  </div>
                @endif
              </div>
              
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">{{ $story['title'] }}</h5>
                <p class="card-text text-muted small">by {{ $story['author'] }}</p>
                
                <div class="mb-2">
                  @if (!empty($story['genre']) && is_array($story['genre']))
                    @foreach(array_slice($story['genre'], 0, 2) as $genre)
                      <span class="badge {{ App\Http\Controllers\BrowseController::getGenreColor($genre) }} me-1 mb-1">{{ $genre }}</span>
                    @endforeach
                  @endif
                </div>
                
                <p class="card-text flex-grow-1">{{ $story['description'] }}</p>
                
                <div class="story-stats d-flex justify-content-between align-items-center mt-auto">
                  <div>
                    <small class="text-muted">{{ App\Http\Controllers\BrowseController::formatReads($story['reads']) }} reads</small>
                    <small class="text-muted ms-2"><i class="fas fa-star me-1"></i>{{ number_format($story['rating'], 1) }}</small>
                  </div>
                  @if($hideNsfw)
                    <button class="btn btn-secondary btn-sm" disabled><i class="fas fa-lock me-1"></i>Locked</button>
                  @else
                    <button class="btn btn-main btn-sm" onclick="viewStory({{ $story['id'] }})">Read Story</button>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center py-5">
            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No stories available yet</h4>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Sticky Footer - This will stay at bottom -->
  <footer class="bg-dark text-white">
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

  <!-- Settings Modal -->
  @auth
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
                    <input type="checkbox" id="modalShowNsfw" {{ $user_settings['show_nsfw'] ?? false ? 'checked' : '' }}>
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
  @endauth

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function viewStory(id) {
      window.location.href = '/stories/' + id;
    }

    // Filtering functionality
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const genreFilter = document.getElementById('genreFilter');
      const storyItems = document.querySelectorAll('.story-item');
      const searchButton = document.getElementById('searchButton');
      const storiesContainer = document.getElementById('storiesContainer');
      
      let filterTimeout;
      
      // Debounced filter function
      function filterStories() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
          performFiltering();
        }, 300);
      }
      
      function performFiltering() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedGenre = genreFilter ? genreFilter.value : '';
        
        let visibleCount = 0;
        
        storyItems.forEach(function(storyItem) {
          const title = storyItem.getAttribute('data-title') || '';
          const author = storyItem.getAttribute('data-author') || '';
          const genreData = storyItem.getAttribute('data-genre') || '[]';
          
          // Parse genre JSON
          let genres = [];
          try {
            genres = JSON.parse(genreData);
          } catch (e) {
            genres = [];
          }
          
          let shouldShow = true;
          
          // Apply search filter
          if (searchTerm) {
            const matchesTitle = title.includes(searchTerm);
            const matchesAuthor = author.includes(searchTerm);
            shouldShow = matchesTitle || matchesAuthor;
          }
          
          // Apply genre filter
          if (shouldShow && selectedGenre) {
            shouldShow = genres.includes(selectedGenre);
          }
          
          // Show/hide story
          if (shouldShow) {
            storyItem.style.display = 'block';
            visibleCount++;
            
            // Add fade in animation
            setTimeout(() => {
              storyItem.style.opacity = '1';
              storyItem.style.transform = 'translateY(0)';
            }, 10);
          } else {
            storyItem.style.opacity = '0';
            storyItem.style.transform = 'translateY(10px)';
            setTimeout(() => {
              storyItem.style.display = 'none';
            }, 300);
          }
        });
        
        // Show message if no results
        showNoResultsMessage(visibleCount === 0);
      }
      
      function showNoResultsMessage(show) {
        let messageElement = document.getElementById('noResultsMessage');
        
        if (show) {
          if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.id = 'noResultsMessage';
            messageElement.className = 'col-12 text-center py-5';
            messageElement.innerHTML = `
              <i class="fas fa-search fa-3x text-muted mb-3"></i>
              <h4 class="text-muted">No stories found</h4>
              <p class="text-muted">Try adjusting your search or filter criteria</p>
            `;
            
            // Hide all story items first
            storyItems.forEach(item => {
              item.style.display = 'none';
            });
            
            // Insert the message at the beginning of the container
            storiesContainer.insertBefore(messageElement, storiesContainer.firstChild);
          }
        } else if (messageElement) {
          // Remove message and ensure all stories are checkable
          messageElement.remove();
          
          // Show all story items first (they'll be filtered again in performFiltering)
          storyItems.forEach(item => {
            item.style.display = 'block';
          });
        }
      }
      
      // Event listeners
      if (searchInput) {
        searchInput.addEventListener('input', filterStories);
        searchInput.addEventListener('keyup', function(event) {
          if (event.key === 'Enter') {
            performFiltering();
          }
        });
      }
      
      if (genreFilter) {
        genreFilter.addEventListener('change', filterStories);
      }
      
      if (searchButton) {
        searchButton.addEventListener('click', function() {
          performFiltering();
        });
      }
      
      // Settings modal toggle
      const modalToggle = document.getElementById('modalShowNsfw');
      
      if (modalToggle) {
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
      
      // Initialize all stories as visible
      storyItems.forEach(item => {
        item.style.opacity = '1';
        item.style.transform = 'translateY(0)';
      });
    });
  </script>
</body>
</html>