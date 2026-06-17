@php
    use App\Support\DashboardAccess;
    use App\Support\Media;
    $avatar = Media::url($user->avatar);
    $userBase = route('dashboard');
    $onPublishPage = request()->routeIs('dashboard.publish');
    $access = $access ?? DashboardAccess::for($user);
@endphp
<aside class="sidebar w-64 h-screen flex flex-col shadow-lg transition-all duration-300 z-40 border-r border-gray-100 fixed top-0 left-0 pt-[74px] overflow-y-auto" id="sidebar">
    <div class="sidebar-header">
        <img src="{{ $avatar }}" alt="Avatar" class="sidebar-user-avatar" id="sidebarAvatar" data-view-link="profile">
        <div class="sidebar-user-info">
            <div class="sidebar-user-name" id="sidebarUserName">{{ $user->name }}</div>
            <div class="sidebar-user-role" id="sidebarUserRole">{{ $user->role?->label() }}</div>
        </div>
    </div>
    <ul class="sidebar-menu flex-1 py-6 space-y-1 overflow-y-auto custom-scrollbar">
        @if($access['stats'])
        <li>
            <a href="{{ $userBase }}?view=stats" data-view="stats" class="flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group">
                <i data-lucide="bar-chart-3" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">Statistiques</span>
            </a>
        </li>
        @endif
        @if($access['pendingOwn'])
        <li>
            <a href="{{ $userBase }}?view=validation" data-view="validation" class="flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group">
                <i data-lucide="clock" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">En attente</span>
                <span class="ml-auto bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full" id="pendingBadge" style="display:none;">0</span>
            </a>
        </li>
        @endif
        <li>
            <a href="{{ $userBase }}?view=articles" data-view="articles" @class(['flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group', 'active' => ! $onPublishPage])>
                <i data-lucide="newspaper" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">Tous mes Articles</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dashboard.publish') }}" data-sidebar="publish" @class(['flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group', 'active' => $onPublishPage])>
                <i data-lucide="circle-plus" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">Créer un Article</span>
            </a>
        </li>
        @if($access['ownPayments'])
        <li>
            <a href="{{ $userBase }}?view=payments" data-view="payments" class="flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group">
                <i data-lucide="banknote" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">Mes Paiements</span>
            </a>
        </li>
        @endif
        @if($access['subscriptions'] ?? true)
        <li>
            <a href="{{ $userBase }}?view=subscriptions" data-view="subscriptions" class="flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group">
                <i data-lucide="credit-card" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">Mon Abonnement</span>
            </a>
        </li>
        @endif
        @if($access['ownAds'])
        <li>
            <a href="{{ $userBase }}?view=ads" data-view="ads" class="flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group">
                <i data-lucide="megaphone" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">Mes Publicités</span>
            </a>
        </li>
        @endif
        @if($access['adRatesView'])
        <li>
            <a href="{{ $userBase }}?view=ads-pricing" data-view="ads-pricing" class="flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group">
                <i data-lucide="tags" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">Tarifs Pub</span>
            </a>
        </li>
        @endif
        <li>
            <a href="{{ $userBase }}?view=profile" data-view="profile" class="flex items-center px-6 py-3.5 transition-all duration-200 border-l-4 border-transparent group">
                <i data-lucide="circle-user" class="lucide-icon text-xl mr-4 sidebar-link-icon transition-colors" aria-hidden="true"></i>
                <span class="font-medium">Mon Profil</span>
            </a>
        </li>
        <li class="mt-8 border-t border-gray-100 pt-6">
            <a href="#" id="logoutLink" class="flex items-center px-6 py-3 transition-all duration-200 group mx-4 rounded-lg shadow-sm hover:shadow-md" style="background-color: #d11810; color: #ffffff;">
                <i data-lucide="log-out" class="lucide-icon text-xl mr-3 group-hover:translate-x-1 transition-transform" aria-hidden="true"></i>
                <span class="font-medium">Déconnexion</span>
            </a>
        </li>
    </ul>
</aside>
