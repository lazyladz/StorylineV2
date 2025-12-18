<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Settings - Storyline</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}" />
  <style>
    .settings-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .setting-item {
        padding: 1.5rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .setting-item:last-child {
        border-bottom: none;
    }
    
    .setting-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .setting-description {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }
    
    .form-switch .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .settings-section {
        margin-bottom: 2rem;
    }

    .section-header {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #667eea;
    }

    .auto-save-indicator {
        display: none;
        font-size: 0.9rem;
        color: #28a745;
    }

    .auto-save-indicator.saving {
        color: #ffc107;
    }

    .auto-save-indicator.error {
        color: #dc3545;
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

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
        aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-th-large me-1"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('write') }}"><i class="fas fa-pen me-1"></i>Write</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('browse') }}"><i class="fas fa-compass me-1"></i>Browse</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('mystories') }}"><i class="fas fa-book me-1"></i>My Stories</a></li>
          
          <li class="nav-item dropdown ms-2">
            <a class="nav-link p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold;">
                <span id="userInitial">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="{{ route('mystories') }}"><i class="fas fa-book me-2"></i>My Stories</a></li>
              <li><a class="dropdown-item active" href="{{ route('settings') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
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

  <!-- Page Header -->
  <div class="page-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 0; margin-bottom: 2rem;">
    <div class="container">
      <h1><i class="fas fa-cog me-2"></i>Settings</h1>
      <p>Customize your Storyline experience</p>
      <div class="auto-save-indicator" id="autoSaveIndicator">
        <i class="fas fa-circle-notch fa-spin"></i>
        <span id="autoSaveText">Saving...</span>
      </div>
    </div>
  </div>

  <!-- Page Content -->
  <div class="container mb-5">
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <div class="card settings-card">
          <div class="card-body p-0">
            
            <!-- Content Preferences Section -->
            <div class="settings-section">
              <div class="setting-item">
                <h5 class="section-header">
                  <i class="fas fa-eye me-2"></i>Content Preferences
                </h5>
              </div>
              
              <div class="setting-item" data-setting="show_nsfw">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="setting-title">
                      <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                      18+ Content Filter
                    </h5>
                    <p class="setting-description">
                      Control whether you want to see stories marked as 18+ (Not Safe For Work).
                      When disabled, stories containing mature themes will be hidden from browse.
                    </p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input setting-toggle" 
                           type="checkbox" 
                           id="showNsfw" 
                           data-setting="show_nsfw"
                           {{ $show_nsfw ? 'checked' : '' }}>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notification Preferences Section -->
            <div class="settings-section">
              <div class="setting-item">
                <h5 class="section-header">
                  <i class="fas fa-bell me-2"></i>Notifications
                </h5>
              </div>
              
              <div class="setting-item" data-setting="email_notifications">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="setting-title">
                      <i class="fas fa-envelope me-2 text-primary"></i>
                      Email Notifications
                    </h5>
                    <p class="setting-description">
                      Receive email updates about new comments, likes, and story milestones.
                    </p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input setting-toggle" 
                           type="checkbox" 
                           id="emailNotifications"
                           data-setting="email_notifications"
                           {{ ($email_notifications ?? true) ? 'checked' : '' }}>
                  </div>
                </div>
              </div>
            </div>

            <!-- Appearance Section -->
            <div class="settings-section">
              <div class="setting-item">
                <h5 class="section-header">
                  <i class="fas fa-palette me-2"></i>Appearance
                </h5>
              </div>
              
              <div class="setting-item" data-setting="dark_mode">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="setting-title">
                      <i class="fas fa-moon me-2 text-info"></i>
                      Dark Mode
                    </h5>
                    <p class="setting-description">
                      Switch to a darker color scheme that's easier on the eyes. (Coming soon)
                    </p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input setting-toggle" 
                           type="checkbox" 
                           id="darkMode"
                           data-setting="dark_mode"
                           {{ ($dark_mode ?? false) ? 'checked' : '' }}
                           disabled>
                  </div>
                </div>
              </div>
            </div>

            <!-- Editor Preferences Section -->
            <div class="settings-section">
              <div class="setting-item">
                <h5 class="section-header">
                  <i class="fas fa-pen-fancy me-2"></i>Editor
                </h5>
              </div>
              
              <div class="setting-item" data-setting="auto_save">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="setting-title">
                      <i class="fas fa-save me-2 text-success"></i>
                      Auto-save Drafts
                    </h5>
                    <p class="setting-description">
                      Automatically save your work while writing to prevent data loss.
                    </p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input setting-toggle" 
                           type="checkbox" 
                           id="autoSave"
                           data-setting="auto_save"
                           {{ ($auto_save ?? true) ? 'checked' : '' }}>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="setting-item">
              <div class="d-flex gap-2">
                <button class="btn btn-primary" id="saveSettings">
                  <i class="fas fa-save me-2"></i>Save All Settings
                </button>
                <button class="btn btn-outline-secondary" id="resetSettings">
                  <i class="fas fa-undo me-2"></i>Reset to Defaults
                </button>
              </div>
              <div id="saveMessage" class="mt-3" style="display: none;"></div>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white py-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <a class="navbar-brand text-white mb-3 d-inline-block" href="{{ route('home') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-book me-2"
              viewBox="0 0 16 16">
              <path
                d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z" />
            </svg>
            Storyline
          </a>
          <p class="text-white-50">
            Where stories come alive. Discover new tales, write your own, and connect with readers everywhere.
          </p>
        </div>
        <div class="col-md-6 text-md-end">
          <p class="text-white-50 mb-1">&copy; 2025 Storyline. All rights reserved.</p>
          <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> for storytellers</p>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Settings manager
    const SettingsManager = {
      autoSaveTimeout: null,
      autoSaveDelay: 1000, // 1 second delay for auto-save
      
      init() {
        this.bindEvents();
        this.loadInitialState();
      },
      
      bindEvents() {
        // Individual toggle auto-save
        document.querySelectorAll('.setting-toggle').forEach(toggle => {
          toggle.addEventListener('change', (e) => {
            if (!e.target.disabled) {
              this.autoSave(e.target.dataset.setting, e.target.checked);
            }
          });
        });
        
        // Manual save button
        document.getElementById('saveSettings')?.addEventListener('click', () => {
          this.saveAllSettings();
        });
        
        // Reset button
        document.getElementById('resetSettings')?.addEventListener('click', () => {
          this.resetSettings();
        });
      },
      
      loadInitialState() {
        console.log('Settings loaded');
      },
      
      autoSave(settingKey, value) {
        clearTimeout(this.autoSaveTimeout);
        this.showAutoSaveIndicator('saving');
        
        this.autoSaveTimeout = setTimeout(() => {
          this.saveSetting(settingKey, value, true);
        }, this.autoSaveDelay);
      },
      
      async saveSetting(key, value, isAutoSave = false) {
        try {
          const response = await fetch('{{ route("settings.update") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ [key]: value })
          });
          
          const result = await response.json();
          
          if (result.success) {
            if (isAutoSave) {
              this.showAutoSaveIndicator('success');
              setTimeout(() => this.hideAutoSaveIndicator(), 2000);
            }
            console.log(`Setting ${key} saved successfully`);
          } else {
            throw new Error(result.error || 'Failed to save');
          }
        } catch (error) {
          console.error('Error saving setting:', error);
          if (isAutoSave) {
            this.showAutoSaveIndicator('error');
            setTimeout(() => this.hideAutoSaveIndicator(), 3000);
          }
          throw error;
        }
      },
      
      async saveAllSettings() {
        const saveBtn = document.getElementById('saveSettings');
        const saveMessage = document.getElementById('saveMessage');
        
        // Collect all settings
        const settings = {};
        document.querySelectorAll('.setting-toggle').forEach(toggle => {
          if (!toggle.disabled) {
            settings[toggle.dataset.setting] = toggle.checked;
          }
        });
        
        // Show loading state
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        saveBtn.disabled = true;
        
        try {
          const response = await fetch('{{ route("settings.update") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(settings)
          });
          
          const result = await response.json();
          
          if (result.success) {
            saveMessage.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>All settings saved successfully!</div>';
            saveMessage.style.display = 'block';
            
            setTimeout(() => {
              saveMessage.style.display = 'none';
            }, 3000);
          } else {
            throw new Error(result.error || 'Unknown error');
          }
        } catch (error) {
          console.error('Error saving settings:', error);
          saveMessage.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error: ${error.message}</div>`;
          saveMessage.style.display = 'block';
        } finally {
          saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save All Settings';
          saveBtn.disabled = false;
        }
      },
      
      async resetSettings() {
        if (!confirm('Are you sure you want to reset all settings to their default values?')) {
          return;
        }
        
        const resetBtn = document.getElementById('resetSettings');
        resetBtn.disabled = true;
        resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting...';
        
        try {
          const response = await fetch('{{ route("settings.reset") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
          });
          
          const result = await response.json();
          
          if (result.success) {
            // Update UI with default values
            if (result.settings) {
              Object.entries(result.settings).forEach(([key, value]) => {
                const toggle = document.querySelector(`[data-setting="${key}"]`);
                if (toggle && !toggle.disabled) {
                  toggle.checked = value;
                }
              });
            }
            
            alert('Settings reset to defaults successfully!');
          } else {
            throw new Error(result.error || 'Failed to reset settings');
          }
        } catch (error) {
          console.error('Error resetting settings:', error);
          alert('Error resetting settings: ' + error.message);
        } finally {
          resetBtn.disabled = false;
          resetBtn.innerHTML = '<i class="fas fa-undo me-2"></i>Reset to Defaults';
        }
      },
      
      showAutoSaveIndicator(status) {
        const indicator = document.getElementById('autoSaveIndicator');
        const text = document.getElementById('autoSaveText');
        
        indicator.style.display = 'block';
        indicator.className = 'auto-save-indicator ' + status;
        
        if (status === 'saving') {
          text.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i>Saving...';
        } else if (status === 'success') {
          text.innerHTML = '<i class="fas fa-check-circle me-1"></i>Saved!';
        } else if (status === 'error') {
          text.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Error saving';
        }
      },
      
      hideAutoSaveIndicator() {
        const indicator = document.getElementById('autoSaveIndicator');
        indicator.style.display = 'none';
      }
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
      SettingsManager.init();
    });
  </script>
</body>
</html>