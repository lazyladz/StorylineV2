<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Stories - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background-color: #f8f9fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
      transition: all 0.3s ease;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #1c2333;
      color: #fff;
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
    .nav-link.active {
      background-color: #667eea;
      color: white !important;
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
        <li class="nav-item"><a href="{{ route('admin.users') }}" class="nav-link"><i class="fas fa-users me-2"></i>Manage Users</a></li>
        <li class="nav-item"><a href="{{ route('admin.stories') }}" class="nav-link active"><i class="fas fa-book me-2"></i>Manage Stories</a></li>
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
          <strong id="sidebarUserName">{{ auth()->user()->name ?? 'Admin' }}</strong><br>
          <small id="sidebarUserEmail">{{ auth()->user()->email ?? 'Admin Email' }}</small>
        </button>
      </div>
    </nav>

    <!-- Story Detail Modal -->
    <div class="modal fade" id="storyDetailModal" tabindex="-1" aria-labelledby="storyDetailLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="storyDetailLabel">Story Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h4 id="detailTitle" class="mb-3"></h4>
            <div class="row mb-3">
              <div class="col-md-6">
                <p class="mb-2"><strong>Author:</strong> <span id="detailAuthor"></span></p>
                <p class="mb-2"><strong>Genre:</strong> <span id="detailGenre"></span></p>
                <p class="mb-2"><strong>Chapters:</strong> <span id="detailChaptersCount"></span></p>
              </div>
              <div class="col-md-6">
                <p class="mb-2"><strong>Reads:</strong> <span id="detailReads"></span></p>
                <p class="mb-2"><strong>Date Created:</strong> <span id="detailDate"></span></p>
                <p class="mb-2"><strong>Status:</strong> <span id="detailStatus" class="badge bg-success">Published</span></p>
              </div>
            </div>
            <div class="mb-3">
              <h6>Description</h6>
              <p id="detailDescription" class="text-muted"></p>
            </div>
            <div class="mb-3">
              <h6>Chapter List</h6>
              <div id="detailChapterList" class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Story Modal -->
    <div class="modal fade" id="editStoryModal" tabindex="-1" aria-labelledby="editStoryLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" action="{{ route('admin.stories.update') }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title" id="editStoryLabel">Edit Story</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="story_id" id="editStoryId">
              
              <div class="mb-3">
                <label for="editTitle" class="form-label">Title</label>
                <input type="text" class="form-control" id="editTitle" name="title" required>
              </div>
              
              <div class="mb-3">
                <label for="editAuthor" class="form-label">Author</label>
                <input type="text" class="form-control" id="editAuthor" name="author" required>
              </div>
              
              <div class="mb-3">
                <label for="editDescription" class="form-label">Description</label>
                <textarea class="form-control" id="editDescription" name="description" rows="4"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteConfirmLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this story? This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <form method="POST" action="{{ route('admin.stories.delete') }}" id="deleteForm">
              @csrf
              <input type="hidden" name="story_id" id="deleteStoryId">
              <button type="submit" class="btn btn-danger">Delete Story</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark" style="font-size: 1.5rem;">Manage Stories</h2>
        <span class="text-muted" style="font-size: 0.9rem;">
          Welcome back, <span class="fw-bold text-primary">{{ auth()->user()->name ?? 'Admin' }}</span>!
        </span>
      </div>
      
      <!-- Messages -->
      @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="font-size: 0.9rem; padding: 0.75rem;">
          <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif
      
      @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 0.9rem; padding: 0.75rem;">
          <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <!-- Stats Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="top-card text-center">
            <h5 class="text-muted" style="font-size: 0.9rem;">Total Stories</h5>
            <div class="stats-number text-primary">{{ $totalStories }}</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card text-center">
            <h5 class="text-muted" style="font-size: 0.9rem;">Total Reads</h5>
            <div class="stats-number text-success">{{ $totalReads }}</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card text-center">
            <h5 class="text-muted" style="font-size: 0.9rem;">Genres</h5>
            <div class="stats-number text-info">{{ count($genreStats) }}</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="top-card text-center">
            <h5 class="text-muted" style="font-size: 0.9rem;">Avg Reads</h5>
            <div class="stats-number text-warning">{{ $totalStories > 0 ? round($totalReads / $totalStories) : 0 }}</div>
          </div>
        </div>
      </div>

      <!-- Search and Filter Section -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body" style="padding: 1rem;">
          <div class="row">
            <div class="col-md-8">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Search stories..." id="searchInput" style="border-radius: 6px;">
                <button class="btn btn-primary" type="button" onclick="searchStories()" style="border-radius: 6px; margin-left: 0.5rem;">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex justify-content-end">
                <select class="form-select" id="genreFilter" onchange="filterStories()" style="border-radius: 6px;">
                  <option value="all">All Genres</option>
                  @foreach ($genreStats as $genre => $count)
                    <option value="{{ $genre }}">{{ $genre }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stories Grid (Card Version) -->
      <div class="card shadow-sm border-0">
        <div class="card-body" style="padding: 1rem;">
          <h5 class="card-title mb-3 text-dark" style="font-size: 1.1rem;">Stories List</h5>
          
          <div id="storiesContainer">
            @if (!empty($stories))
              <div class="stories-grid" id="storiesGrid">
                @foreach ($stories as $story)
                  @php
                    // Process story data
                    $genres = [];
                    if (isset($story['genre'])) {
                        if (is_string($story['genre'])) {
                            $genre_json = stripslashes($story['genre']);
                            $genres = json_decode($genre_json, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $genres = ['Unknown'];
                            }
                        } else {
                            $genres = $story['genre'];
                        }
                    }
                    
                    $chapters = [];
                    $chapterCount = 0;
                    if (isset($story['chapters'])) {
                        if (is_string($story['chapters'])) {
                            $chapters_json = stripslashes($story['chapters']);
                            $chapters = json_decode($chapters_json, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $chapters = [];
                            }
                        } else {
                            $chapters = $story['chapters'];
                        }
                        $chapterCount = is_array($chapters) ? count($chapters) : 0;
                    }
                    
                    // Get cover image or use default
                    $coverImage = $story['cover_image'] ?? 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                    $isNsfw = $story['is_nsfw'] ?? false;
                    
                    // Prepare data for JavaScript
                    $storyId = $story['id'];
                    $storyTitle = htmlspecialchars($story['title'] ?? '', ENT_QUOTES, 'UTF-8');
                    $storyAuthor = htmlspecialchars($story['author'] ?? '', ENT_QUOTES, 'UTF-8');
                    $storyDescription = htmlspecialchars($story['description'] ?? '', ENT_QUOTES, 'UTF-8');
                    $storyReads = $story['reads'] ?? 0;
                    $storyDate = $story['created_at'] ?? '';
                    
                    // Convert to JSON and escape for HTML attributes
                    $genresJson = htmlspecialchars(json_encode($genres), ENT_QUOTES, 'UTF-8');
                    $chaptersJson = htmlspecialchars(json_encode($chapters), ENT_QUOTES, 'UTF-8');
                    
                    // Store all data in one JSON object for the card
                    $storyData = htmlspecialchars(json_encode([
                        'id' => $storyId,
                        'title' => $story['title'] ?? '',
                        'author' => $story['author'] ?? '',
                        'description' => $story['description'] ?? '',
                        'reads' => $storyReads,
                        'date' => $storyDate,
                        'cover_image' => $coverImage,
                        'genres' => $genres,
                        'chapters' => $chapters,
                        'chapter_count' => $chapterCount
                    ]), ENT_QUOTES, 'UTF-8');
                  @endphp
                  
                  <div class="story-card-wrapper" 
                       data-story="{{ $storyData }}"
                       data-title="{{ strtolower($story['title'] ?? '') }}" 
                       data-author="{{ strtolower($story['author'] ?? '') }}" 
                       data-genre="{{ strtolower(implode(' ', $genres)) }}">
                    <div class="story-card">
                      <div class="card-img-container">
                        <img src="{{ $coverImage }}" class="card-img" alt="{{ $story['title'] ?? 'Story Cover' }}">
                        @if($isNsfw)
                          <span class="badge bg-danger nsfw-badge"><i class="fas fa-exclamation-triangle"></i> 18+</span>
                        @endif
                        <div class="card-actions">
                          <button class="action-btn btn-edit" onclick="event.stopPropagation(); editStory('{{ $storyId }}', '{{ $storyTitle }}', '{{ $storyAuthor }}', '{{ $storyDescription }}')">
                            <i class="fas fa-edit"></i>
                          </button>
                          <button type="button" class="action-btn btn-delete" onclick="event.stopPropagation(); confirmDelete('{{ $storyId }}')">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </div>
                      <div class="card-body">
                        <h6 class="story-title" title="{{ $story['title'] ?? 'Untitled' }}">
                          {{ $story['title'] ?? 'Untitled' }}
                        </h6>
                        <p class="story-author">by {{ $story['author'] ?? 'Unknown Author' }}</p>
                        
                        <div class="story-genres">
                          @foreach(array_slice($genres, 0, 2) as $genre)
                            <span class="badge bg-primary genre-badge">{{ $genre }}</span>
                          @endforeach
                          @if(count($genres) > 2)
                            <span class="badge bg-secondary genre-badge">+{{ count($genres) - 2 }}</span>
                          @endif
                        </div>
                        
                        <div class="story-stats">
                          <span class="story-reads">{{ $storyReads }} reads</span>
                          <span class="story-chapters">{{ $chapterCount }} ch</span>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="empty-state">
                <i class="fas fa-book fa-2x mb-2"></i>
                <p class="text-muted" style="font-size: 0.9rem;">No stories found in the database.</p>
              </div>
            @endif
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Search stories
function searchStories() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const storyCards = document.querySelectorAll('.story-card-wrapper');
  let visibleCount = 0;
  
  storyCards.forEach(card => {
    const title = card.getAttribute('data-title');
    const author = card.getAttribute('data-author');
    const genre = card.getAttribute('data-genre');
    
    if (title.includes(searchTerm) || author.includes(searchTerm) || genre.includes(searchTerm)) {
      card.style.display = 'block';
      visibleCount++;
    } else {
      card.style.display = 'none';
    }
  });
}

// Filter stories by genre
function filterStories() {
  const genreFilter = document.getElementById('genreFilter').value.toLowerCase();
  const storyCards = document.querySelectorAll('.story-card-wrapper');
  
  storyCards.forEach(card => {
    const genre = card.getAttribute('data-genre');
    
    if (genreFilter === 'all' || genre.includes(genreFilter)) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
}

// View story details - UPDATED for card layout
function viewStory(id, title, author, genresJson, reads, date, description, chaptersJson, chapterCount) {
  try {
    // Parse JSON strings back to objects
    const genres = JSON.parse(genresJson);
    const chapters = JSON.parse(chaptersJson);
    
    // Set modal content
    document.getElementById('detailTitle').textContent = title;
    document.getElementById('detailAuthor').textContent = author;
    document.getElementById('detailReads').textContent = reads;
    document.getElementById('detailChaptersCount').textContent = chapterCount + ' chapter' + (chapterCount != 1 ? 's' : '');
    
    // Format date
    const formattedDate = new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
    document.getElementById('detailDate').textContent = formattedDate || 'Unknown date';
    
    // Set genres
    const genreContainer = document.getElementById('detailGenre');
    genreContainer.innerHTML = '';
    if (genres && genres.length > 0) {
      genres.forEach(genre => {
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary me-1';
        badge.textContent = genre;
        genreContainer.appendChild(badge);
      });
    } else {
      genreContainer.textContent = 'No genres specified';
    }
    
    // Set description
    const descriptionElement = document.getElementById('detailDescription');
    descriptionElement.textContent = description || 'No description available.';
    
    // Set chapters list
    const chapterListContainer = document.getElementById('detailChapterList');
    chapterListContainer.innerHTML = '';
    
    if (chapters && chapters.length > 0) {
      chapters.forEach((chapter, index) => {
        const chapterDiv = document.createElement('div');
        chapterDiv.className = 'chapter-item';
        
        // Handle chapter title safely
        const chapterTitle = chapter.title || 'Untitled Chapter';
        chapterDiv.innerHTML = `
          <strong>Chapter ${index + 1}:</strong> ${chapterTitle}
        `;
        chapterListContainer.appendChild(chapterDiv);
      });
    } else {
      chapterListContainer.innerHTML = '<p class="text-muted">No chapters available.</p>';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('storyDetailModal'));
    modal.show();
  } catch (error) {
    console.error('Error displaying story:', error);
    alert('Error loading story details. Please try again.');
  }
}

// Add click event to cards
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.story-card').forEach(card => {
    card.addEventListener('click', function(e) {
      // Don't trigger if clicking on action buttons
      if (e.target.closest('.card-actions')) return;
      
      const wrapper = this.closest('.story-card-wrapper');
      const storyData = JSON.parse(wrapper.getAttribute('data-story'));
      
      // Call the existing viewStory function with parsed data
      viewStory(
        storyData.id,
        storyData.title,
        storyData.author,
        JSON.stringify(storyData.genres),
        storyData.reads,
        storyData.date,
        storyData.description,
        JSON.stringify(storyData.chapters),
        storyData.chapter_count
      );
    });
  });
});

// Edit story
function editStory(id, title, author, description) {
  document.getElementById('editStoryId').value = id;
  document.getElementById('editTitle').value = title;
  document.getElementById('editAuthor').value = author;
  document.getElementById('editDescription').value = description || '';
  
  const modal = new bootstrap.Modal(document.getElementById('editStoryModal'));
  modal.show();
}

// Confirm delete
function confirmDelete(storyId) {
  document.getElementById('deleteStoryId').value = storyId;
  const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
  modal.show();
}

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);

// Add Enter key support for search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    searchStories();
  }
});

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