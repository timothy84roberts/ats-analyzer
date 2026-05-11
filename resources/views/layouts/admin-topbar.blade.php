<header class="admin-topbar">
    <div class="admin-topbar__left">
        
    </div>
    <div class="admin-topbar__right">
        <button type="button" class="admin-icon-btn" data-theme-toggle aria-label="{{ __('Toggle theme') }}">
            <span class="admin-theme-moon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
            </span>
            <span class="admin-theme-sun" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
            </span>
        </button>
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" class="admin-icon-btn admin-icon-btn--avatar" aria-label="{{ __('Account menu') }}">
                    <span class="admin-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </button>
            </x-slot>
            <x-slot name="content">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600 text-left">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
                </div>
                <x-dropdown-link :href="route('settings.index')">{{ __('Settings') }}</x-dropdown-link>
                <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
