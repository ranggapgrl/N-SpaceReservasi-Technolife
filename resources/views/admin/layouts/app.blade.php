<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Admin') | Needs Study Space</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fbf9f8] font-sans min-h-screen text-[#1b1c1c]" x-data="{ sidebarOpen: false }">

    <!-- Mobile Overlay -->
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 z-40 md:hidden"
    ></div>

    <div class="flex h-screen overflow-hidden relative">

<!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-white text-[#1b1c1c] flex-shrink-0 flex flex-col h-full transition-transform duration-300 ease-in-out md:translate-x-0 border-r border-gray-200 shadow-xs"
        >
            <!-- Logo -->
            <div class="h-20 flex items-center px-6 border-b border-gray-100 flex-shrink-0 justify-between">
                <h2 class="font-bold text-xl tracking-wide font-headline text-[#1b1c1c]">NEEDS study <span style="color: #b1002c">space</span><span style="color: #b1002c;">.</span></h2>
                <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="flex-grow py-6 px-4 space-y-1.5 overflow-y-auto font-sans text-sm">

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center px-4 py-3 rounded-xl font-medium transition-colors
                          {{ request()->routeIs('admin.dashboard') ? 'text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#1b1c1c]' }}"
                   style="{{ request()->routeIs('admin.dashboard') ? 'background-color: #b1002c;' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                        </path>
                    </svg>
                    Dashboard
                </a>

                <!-- Bookings -->
                <a href="{{ route('admin.bookings.index') }}"
                   class="flex items-center px-4 py-3 rounded-xl font-medium transition-colors
                          {{ request()->routeIs('admin.bookings.*') ? 'text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#1b1c1c]' }}"
                   style="{{ request()->routeIs('admin.bookings.*') ? 'background-color: #b1002c;' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Bookings
                </a>

                <!-- Produk -->
                <a href="{{ route('admin.products.index') }}"
                   class="flex items-center px-4 py-3 rounded-xl font-medium transition-colors
                          {{ request()->routeIs('admin.products.*') ? 'text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#1b1c1c]' }}"
                   style="{{ request()->routeIs('admin.products.*') ? 'background-color: #b1002c;' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                        </path>
                    </svg>
                    Produk
                </a>

                <!-- Kelola Tanggal -->
                <a href="{{ route('admin.blocked-dates.index') }}"
                   class="flex items-center px-4 py-3 rounded-xl font-medium transition-colors
                          {{ request()->routeIs('admin.blocked-dates.*') ? 'text-white shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-[#1b1c1c]' }}"
                   style="{{ request()->routeIs('admin.blocked-dates.*') ? 'background-color: #b1002c;' : '' }}">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Kelola Tanggal
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-gray-100 flex-shrink-0">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-[#1b1c1c] rounded-xl font-medium transition-colors w-full font-sans text-sm">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="flex-grow flex flex-col min-w-0 h-full overflow-hidden">

            <!-- Top Header -->
            <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 md:px-8 flex-shrink-0">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Hamburger (mobile) -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg sm:text-xl font-semibold text-[#1b1c1c] truncate font-headline">@yield('page_title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-[#1b1c1c] hidden sm:block font-sans">{{ Auth::user()->name }}</span>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm" style="background-color: #b1002c;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-grow overflow-y-auto p-4 sm:p-6 md:p-8 bg-[#fbf9f8]">

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center space-x-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center space-x-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>