<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Profile - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    :root {
      --primary: #6d28d9;
      --primary-dark: #5b21b6;
      --secondary: #f59e0b;
      --light: #f8fafc;
      --dark: #1e293b;
      --gray: #64748b;
      --success: #10b981;
    }
    
    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background-color: #f8fafc;
      color: var(--dark);
    }
    
    .navbar {
      background-color: white;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
      padding: 1rem 0;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--primary);
      display: flex;
      align-items: center;
    }
    
    .nav-link {
      font-weight: 500;
      margin: 0 0.5rem;
      color: var(--dark);
      transition: color 0.3s;
    }
    
    .nav-link:hover, .nav-link.active {
      color: var(--primary);
    }
    
    .btn-main {
      background-color: var(--primary);
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
    }
    
    .btn-main:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(109, 40, 217, 0.3);
    }
    
    .profile-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 3rem 0 2rem;
      margin-bottom: 2rem;
    }
    
    .profile-avatar {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .profile-name {
      font-weight: 700;
      margin-bottom: 0.5rem;
      font-size: 2.5rem;
    }
    
    .profile-bio {
      font-size: 1.1rem;
      opacity: 0.9;
      margin-bottom: 1.5rem;
      max-width: 600px;
    }
    
    .stats-section {
      background-color: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 2rem;
    }
    
    .stat-item {
      text-align: center;
      padding: 1.5rem;
      transition: all 0.3s;
      border-radius: 8px;
    }
    
    .stat-item:hover {
      background-color: rgba(109, 40, 217, 0.05);
      transform: translateY(-5px);
    }
    
    .stat-icon {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }
    
    .stat-label {
      font-size: 0.9rem;
      color: var(--gray);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .stories-section {
      background-color: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      margin-bottom: 2rem;
    }
    
    .section-title {
      font-weight: 700;
      margin-bottom: 1.5rem;
      position: relative;
      padding-bottom: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      border-radius: 2px;
    }
    
    .stories-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
    }
    
    .story-card {
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s;
      height: 100%;
    }
    
    .story-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }
    
    .story-image {
      height: 180px;
      object-fit: cover;
      transition: transform 0.5s;
    }
    
    .story-card:hover .story-image {
      transform: scale(1.05);
    }
    
    .story-content {
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: calc(100% - 180px);
    }
    
    .story-title {
      font-weight: 600;
      margin-bottom: 0.5rem;
      font-size: 1.1rem;
      line-height: 1.3;
    }
    
    .story-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      font-size: 0.85rem;
    }
    
    .badge {
      font-size: 0.7rem;
      padding: 0.3rem 0.6rem;
      border-radius: 20px;
    }
    
    .bg-pink {
      background-color: #e83e8c !important;
    }
    
    .story-stats {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 0.75rem;
      font-size: 0.8rem;
    }
    
    .reads {
      color: var(--gray);
    }
    
    .rating {
      color: var(--secondary);
      font-weight: 600;
    }
    
    .action-buttons {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
    }
    
    .btn-action {
      flex: 1;
      padding: 0.5rem;
      font-size: 0.8rem;
      border-radius: 6px;
      transition: all 0.3s;
    }
    
    .btn-view {
      background-color: var(--primary);
      color: white;
      border: none;
    }
    
    .btn-edit {
      background-color: var(--secondary);
      color: white;
      border: none;
    }
    
    .btn-delete {
      background-color: #ef4444;
      color: white;
      border: none;
    }
    
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      background: #f8fafc;
      border-radius: 12px;
      border: 2px dashed #e2e8f0;
    }
    
    .empty-state i {
      font-size: 3rem;
      color: var(--gray);
      margin-bottom: 1rem;
    }
    
    footer {
      background-color: var(--dark);
      color: white;
      padding: 2rem 0 1rem;
      margin-top: 3rem;
    }
    
    .modal-content {
      border: none;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    @media (max-width: 768px) {
      .profile-header {
        padding: 2rem 0 1.5rem;
      }
      
      .profile-name {
        font-size: 2rem;
      }
      
      .profile-avatar {
        width: 120px;
        height: 120px;
      }
      
      .stories-grid {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <!-- Replace the navbar section in your profile.blade.php (resources/views/profile/index.blade.php) with this -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="{{ route('home') }}">
      <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2" viewBox="0 0 16 16">
        <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
      </svg>
      Storyline
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-th-large me-1"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('browse') }}"><i class="fas fa-compass me-1"></i>Browse</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('write') }}"><i class="fas fa-pen me-1"></i>Write</a></li>
        
        <!-- User dropdown with profile picture -->
        <li class="nav-item dropdown ms-2">
          <a class="nav-link p-0" href="#" data-bs-toggle="dropdown">
            <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center" 
                 style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);">
              @if(isset($profile_image) && !str_contains($profile_image ?? '', 'unsplash.com'))
                <img src="{{ $profile_image }}" alt="Profile" id="navProfilePic" style="width: 100%; height: 100%; object-fit: cover;">
              @else
                <span id="userInitial" style="color: white; font-weight: bold;">
                  {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
              @endif
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i>My Profile</a></li>
            <li><a class="dropdown-item" href="{{ route('mystories') }}"><i class="fas fa-book me-2"></i>My Stories</a></li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

  <!-- Profile Header -->
  <div class="profile-header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-3 text-center text-lg-start mb-4 mb-lg-0">
          <img src="{{ $profile_image }}" alt="User Avatar" class="profile-avatar" id="profileAvatar">
          <div class="mt-3">
            <button class="btn btn-main" data-bs-toggle="modal" data-bs-target="#editProfileModal">
              <i class="fas fa-edit me-1"></i>Edit Profile
            </button>
          </div>
        </div>
        <div class="col-lg-9 text-center text-lg-start">
          <h1 class="profile-name" id="profileName">{{ auth()->user()->name }}</h1>
          <p class="profile-bio" id="profileBio">
            {{ $supabaseUser['bio'] ?? 'Passionate storyteller and avid reader. Exploring worlds one story at a time.' }}
          </p>
          <div class="d-flex gap-3 flex-wrap">
            <span class="text-white-80"><i class="fas fa-envelope me-1"></i>{{ auth()->user()->email }}</span>
            <span class="text-white-80"><i class="fas fa-calendar-alt me-1"></i> Joined {{ date('F Y') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <!-- Stats Section -->
    <div class="stats-section">
      <div class="row">
        <div class="col-md-4 col-6">
          <div class="stat-item">
            <i class="fas fa-book-open stat-icon"></i>
            <div class="stat-number">{{ $total_stories }}</div>
            <div class="stat-label">Stories</div>
          </div>
        </div>
        <div class="col-md-4 col-6">
          <div class="stat-item">
            <i class="fas fa-eye stat-icon"></i>
            <div class="stat-number">{{ $total_reads }}</div>
            <div class="stat-label">Total Reads</div>
          </div>
        </div>
        <div class="col-md-4 col-6">
          <div class="stat-item">
            <i class="fas fa-star stat-icon"></i>
            <div class="stat-number">0</div>
            <div class="stat-label">Avg. Rating</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stories Section -->
    <div class="stories-section">
      <h3 class="section-title">
        <span><i class="fas fa-book me-2"></i>My Stories</span>
        <a href="#" class="btn btn-main btn-sm">
          <i class="fas fa-plus me-1"></i>New Story
        </a>
      </h3>
      
      <div class="stories-grid">
        @php
          $profileController = app(App\Http\Controllers\ProfileController::class);
        @endphp
        
        @if (!empty($userStories))
          @foreach ($userStories as $story)
            <div class="story-card">
              <img src="{{ $story['cover_image'] }}" alt="{{ $story['title'] }}" class="story-image">
              <div class="story-content">
                <div>
                  <h4 class="story-title">{{ $story['title'] }}</h4>
                  <div class="story-meta">
                    <div>
                      @if (!empty($story['genre']) && is_array($story['genre']))
                        @foreach ($story['genre'] as $genre)
                          <span class="badge me-1 {{ $profileController->getGenreColor($genre) }}">{{ $genre }}</span>
                        @endforeach
                      @endif
                    </div>
                    <span class="text-muted">{{ count($story['chapters'] ?? []) }} ch</span>
                  </div>
                  <div class="story-stats">
                    <span class="reads">{{ $profileController->formatReads($story['reads']) }} reads</span>
                    <span class="rating">{{ $story['rating'] > 0 ? number_format($story['rating'], 1) . ' ★' : 'Not rated' }}</span>
                  </div>
                </div>
                <div class="action-buttons">
                  <button class="btn btn-action btn-view" onclick="viewStory('{{ $story['id'] }}')">
                    <i class="fas fa-eye me-1"></i>View
                  </button>
                  <button class="btn btn-action btn-edit" onclick="editStory('{{ $story['id'] }}')">
                    <i class="fas fa-edit me-1"></i>Edit
                  </button>
                  <button class="btn btn-action btn-delete" onclick="deleteStory('{{ $story['id'] }}')">
                    <i class="fas fa-trash me-1"></i>Delete
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        @else
          <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h4>No stories yet</h4>
            <p>You haven't published any stories. Start your writing journey today!</p>
            <a href="#" class="btn btn-main">Write Your First Story</a>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white py-4">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <a class="navbar-brand text-white mb-3 d-inline-block" href="{{ route('home') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2" viewBox="0 0 16 16">
              <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
            </svg>
            Storyline
          </a>
          <p class="text-white-50">
            Where stories come alive. Discover new tales, write your own, and connect with readers everywhere.
          </p>
        </div>
        <div class="col-md-6 text-md-end">
          <p class="text-white-50 mb-1">&copy; {{ date('Y') }} Storyline. All rights reserved.</p>
          <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> for storytellers</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Edit Profile Modal -->
  <div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editProfileForm">
            @csrf
            <div class="row">
              <div class="col-md-4 text-center mb-3">
                <img src="{{ $profile_image }}" alt="User Avatar" class="profile-avatar mb-3" id="modalAvatar">
                <div>
                  <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                  <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('profileImageInput').click()">
                    <i class="fas fa-camera me-1"></i>Change Photo
                  </button>
                </div>
              </div>
              <div class="col-md-8">
                <div class="mb-3">
                  <label for="editFirstName" class="form-label">First Name</label>
                  <input type="text" class="form-control" id="editFirstName" value="{{ $supabaseUser['first_name'] ?? explode(' ', auth()->user()->name)[0] }}">
                </div>
                <div class="mb-3">
                  <label for="editLastName" class="form-label">Last Name</label>
                  <input type="text" class="form-control" id="editLastName" value="{{ $supabaseUser['last_name'] ?? (explode(' ', auth()->user()->name)[1] ?? '') }}">
                </div>
                <div class="mb-3">
                  <label for="editEmail" class="form-label">Email</label>
                  <input type="email" class="form-control" id="editEmail" value="{{ auth()->user()->email }}">
                </div>
                <div class="mb-3">
                  <label for="editBio" class="form-label">Bio</label>
                  <textarea class="form-control" id="editBio" rows="4">{{ $supabaseUser['bio'] ?? 'Passionate storyteller and avid reader. Exploring worlds one story at a time.' }}</textarea>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-main" onclick="saveProfile()">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Global variable to store the selected image file
let selectedImageFile = null;

// Wait for page to load
document.addEventListener('DOMContentLoaded', function() {
  
  // Image upload preview
  document.getElementById('profileImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      // Check file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        showNotification('Image must be less than 2MB', 'error');
        return;
      }
      
      // Check file type
      if (!file.type.match('image.*')) {
        showNotification('Please select an image file', 'error');
        return;
      }
      
      // Store the file for later upload
      selectedImageFile = file;
      
      // Show preview
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('modalAvatar').src = e.target.result;
        document.getElementById('profileAvatar').src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });
  
});

// Save profile function
function saveProfile() {
  const firstName = document.getElementById('editFirstName').value;
  const lastName = document.getElementById('editLastName').value;
  const email = document.getElementById('editEmail').value;

  // Validate inputs
  if (!firstName || !lastName || !email) {
    showNotification('Please fill in all fields', 'error');
    return;
  }

  // Show loading state
  const saveButton = document.querySelector('#editProfileModal .btn-main');
  const originalText = saveButton.innerHTML;
  saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
  saveButton.disabled = true;

  // Use FormData to handle file upload
  const formData = new FormData();
  formData.append('first_name', firstName);
  formData.append('last_name', lastName);
  formData.append('email', email);
  formData.append('_token', '{{ csrf_token() }}');
  
  // Add image if one was selected
  if (selectedImageFile) {
    formData.append('profile_image', selectedImageFile);
    console.log('Uploading image:', selectedImageFile.name);
  }

  // Send request
  fetch('{{ route("profile.update") }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
      // DON'T set Content-Type - let browser set it with boundary for FormData
    },
    body: formData
  })
  .then(response => {
    console.log('Response status:', response.status);
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(result => {
    console.log('Result:', result);
    
    if (result.success) {
      // Update profile display
      document.getElementById('profileName').textContent = `${firstName} ${lastName}`;
      document.getElementById('userInitial').textContent = firstName.charAt(0).toUpperCase();
      
      // Update navbar avatar if image was uploaded
      if (result.profile_image_url) {
        const navbarAvatar = document.querySelector('.navbar .rounded-circle');
        if (navbarAvatar) {
          navbarAvatar.innerHTML = `<img src="${result.profile_image_url}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">`;
        }
      }
      
      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
      modal.hide();
      
      // Show success message and reload
      showNotification('Profile updated successfully! Refreshing...', 'success');
      setTimeout(() => window.location.reload(), 1500);
      
      // Reset image file
      selectedImageFile = null;
    } else {
      showNotification(result.error || 'Failed to update profile', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Failed to update profile. Please try again.', 'error');
  })
  .finally(() => {
    // Restore button state
    saveButton.innerHTML = originalText;
    saveButton.disabled = false;
  });
}

// Notification function
function showNotification(message, type = 'success') {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
  notification.style.cssText = `
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  `;
  
  notification.innerHTML = `
    <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
}

// Story action functions
function viewStory(storyId) {
  window.location.href = `/stories/${storyId}`;
}

function editStory(storyId) {
  window.location.href = `/write/${storyId}`;
}

function deleteStory(storyId) {
  if (confirm('Are you sure you want to delete this story?')) {
    console.log('Delete story:', storyId);
    // Add delete functionality here
  }
}
</script>
</body>
</html>