@php
    use App\Support\Media;
    $avatar = Media::url($user->avatar);
    $logo = config('chrononews.brand.assets.logo_admin');
@endphp
<nav class="navbar">
    <div class="navbar-brand">
        <a href="{{ route('home') }}">
            <img src="{{ asset($logo) }}" alt="{{ config('chrononews.name') }}">
        </a>
    </div>
    <div class="navbar-user">
        <div class="navbar-notifications" id="notificationsBtn" role="button" aria-label="Notifications" tabindex="0">
            <i data-lucide="bell" class="lucide-icon" aria-hidden="true"></i>
            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="notifications-header">
                    <i data-lucide="bell" class="lucide-icon" aria-hidden="true"></i>
                    <span>Notifications</span>
                </div>
                <div class="notifications-list" id="notificationsList">
                    <div class="notifications-empty">
                        <i data-lucide="bell-off" class="lucide-icon" aria-hidden="true"></i>
                        <p>Aucune notification</p>
                    </div>
                </div>
                <div class="notifications-footer">
                    <button type="button" class="notifications-view-all" id="notificationsViewAllBtn">
                        Tout afficher
                    </button>
                </div>
            </div>
        </div>
        <div class="navbar-user-info" data-view-link="profile">
            <img src="{{ $avatar }}" alt="Avatar" class="navbar-user-avatar" id="navbarAvatar">
            <div class="navbar-user-details">
                <div class="navbar-user-name" id="navbarUserName">{{ $user->name }}</div>
                <div class="navbar-user-role" id="navbarUserRole">{{ $user->role?->label() }}</div>
            </div>
        </div>
    </div>
</nav>
