{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Plus36 Networks SMS - @yield('title', config('app.name'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background: rgba(126, 211, 196, 0.16);
            transform: translateX(5px);
        }
        .sidebar-item.active {
            background: rgba(126, 211, 196, 0.22);
            color: #06322C;
            box-shadow: inset 3px 0 #3A7B72;
        }
        .sidebar {
            transition: transform 0.3s ease;
        }
        input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]),
        select,
        textarea {
            border: 1px solid #cbd5e1 !important;
            background-color: #fff;
        }
        input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):focus,
        select:focus,
        textarea:focus {
            border-color: #3A7B72 !important;
            outline: 2px solid rgba(58, 123, 114, 0.16);
            outline-offset: 1px;
        }
        input:disabled, select:disabled, textarea:disabled {
            background-color: #f1f5f9;
            color: #64748b;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-[#f4f7f6]" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar" :class="{ 'active': sidebarOpen }">
            @include('layouts.sidebar')
        </div>
        
        <!-- Overlay for mobile -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" 
             class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
             x-transition.opacity></div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            @include('layouts.navigation')
            
            <div class="p-4 lg:p-7">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
    @stack('scripts')
</body>
</html>
