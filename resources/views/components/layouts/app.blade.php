<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'StokKu Gudang' }} - PABWE 2026</title>
    <meta name="description" content="StokKu Gudang membantu Anda mengelola inventaris, memantau ketersediaan barang, dan mengatur batas minimum stok.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @if (app(\Illuminate\Foundation\Vite::class)->isRunningHot())
        @vite('resources/css/app.css')
    @elseif (request()->routeIs('home', 'login', 'register'))
        <style>{!! \Illuminate\Support\Facades\Vite::content('resources/css/app.css') !!}</style>
    @else
        @php($stylesheetUrl = route('assets.css', ['filename' => basename(parse_url(\Illuminate\Support\Facades\Vite::asset('resources/css/app.css'), PHP_URL_PATH))]))
        <link rel="stylesheet" href="{{ $stylesheetUrl }}">
    @endif
    <style>
        [x-cloak] { display: none !important; }
        body { background-image: radial-gradient(#d6d3d1 1px, transparent 1px); background-size: 22px 22px; background-color: #fafaf9; }
        html:has(.modal-scroll-lock),
        body:has(.modal-scroll-lock) { overflow: hidden !important; overscroll-behavior: none; }
    </style>
    @unless (request()->routeIs('home', 'login', 'register'))
        @livewireStyles
    @endunless
    @if (request()->routeIs('stocks.*'))
        <script>
            window.loadToastUIEditor = function () {
                if (window.toastui?.Editor) {
                    return Promise.resolve(window.toastui.Editor);
                }

                if (!window.toastUIEditorPromise) {
                    const stylesheet = document.createElement('link');
                    stylesheet.rel = 'stylesheet';
                    stylesheet.href = 'https://uicdn.toast.com/editor/latest/toastui-editor.min.css';

                    const script = document.createElement('script');
                    script.src = 'https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js';
                    script.async = true;

                    const load = (element) => new Promise((resolve, reject) => {
                        element.addEventListener('load', resolve, { once: true });
                        element.addEventListener('error', () => reject(new Error('Toast UI Editor gagal dimuat.')), { once: true });
                    });

                    window.toastUIEditorPromise = Promise.all([load(stylesheet), load(script)])
                        .then(() => {
                            if (!window.toastui?.Editor) {
                                throw new Error('Toast UI Editor tidak tersedia setelah aset dimuat.');
                            }

                            return window.toastui.Editor;
                        })
                        .catch((error) => {
                            window.toastUIEditorPromise = null;
                            throw error;
                        });

                    document.head.append(stylesheet, script);
                }

                return window.toastUIEditorPromise;
            };
        </script>
    @endif
</head>
<body class="h-full font-sans text-slate-800 antialiased" x-data="{ menuOpen: false }">
    @auth
        @php($authUser = auth()->user())
        <div class="min-h-screen flex flex-col">
            <!-- Top Navbar -->
            <header class="bg-ink text-white sticky top-0 z-40 shadow-lg shadow-black/10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-6">
                    <a href="{{ $authUser->isAdmin() ? route('admin.users') : route('stocks.index') }}" class="flex items-center gap-2.5 shrink-0">
                        <span class="w-9 h-9 rounded-lg bg-teal-400 text-ink flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-6 9 6v11a1 1 0 01-1 1H4a1 1 0 01-1-1V9zm6 12v-7h6v7" /></svg>
                        </span>
                        <span class="font-extrabold tracking-tight text-lg">Stok<span class="text-teal-300">Ku</span> <span class="hidden sm:inline text-xs font-semibold text-stone-400 uppercase tracking-widest">Gudang</span></span>
                    </a>

                    <!-- Desktop nav -->
                    <nav class="hidden md:flex items-center gap-1 flex-1">
                        @if($authUser->isAdmin())
                            <a href="{{ route('admin.users') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('admin.*') ? 'bg-teal-400 text-ink' : 'text-stone-300 hover:bg-white/10 hover:text-white' }}">User Management</a>
                        @endif
                        <a href="{{ route('stocks.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('stocks.*') ? 'bg-teal-400 text-ink' : 'text-stone-300 hover:bg-white/10 hover:text-white' }}">Stok Barang Saya</a>
                        <a href="{{ route('profile') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('profile') ? 'bg-teal-400 text-ink' : 'text-stone-300 hover:bg-white/10 hover:text-white' }}">Profil Saya</a>
                    </nav>

                    <!-- User dropdown -->
                    <div class="relative hidden md:block" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" class="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-full bg-white/10 hover:bg-white/15 transition">
                            @if ($authUser->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($authUser->profile_photo_path))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($authUser->profile_photo_path) }}" alt="{{ $authUser->name }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <span class="w-8 h-8 rounded-full bg-amber-400 text-ink flex items-center justify-center font-bold text-xs">{{ strtoupper(substr($authUser->name, 0, 2)) }}</span>
                            @endif
                            <span class="text-sm font-semibold max-w-[8rem] truncate">{{ $authUser->name }}</span>
                            <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition.opacity class="absolute right-0 mt-2 w-60 rounded-xl bg-white text-slate-800 shadow-2xl ring-1 ring-black/5 overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                                <p class="text-sm font-bold truncate">{{ $authUser->name }}</p>
                                <p class="text-xs text-slate-500 font-mono truncate">&#64;{{ $authUser->username }}</p>
                                <span class="inline-block mt-2 px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider {{ $authUser->isAdmin() ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800' }}">{{ ucfirst($authUser->role) }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile toggle -->
                    <button type="button" @click="menuOpen = !menuOpen" class="md:hidden p-2 rounded-lg text-stone-300 hover:bg-white/10" aria-label="Menu">
                        <svg x-show="!menuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg x-show="menuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Mobile menu -->
                <div x-show="menuOpen" x-cloak x-transition class="md:hidden border-t border-white/10 px-4 py-3 space-y-1">
                    <div class="flex items-center justify-between px-3 py-2 text-sm">
                        <span class="font-semibold">{{ $authUser->name }}</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-amber-400 text-ink">{{ ucfirst($authUser->role) }}</span>
                    </div>
                    @if($authUser->isAdmin())
                        <a href="{{ route('admin.users') }}" class="block px-3 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.*') ? 'bg-teal-400 text-ink' : 'text-stone-300 hover:bg-white/10' }}">User Management</a>
                    @endif
                    <a href="{{ route('stocks.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('stocks.*') ? 'bg-teal-400 text-ink' : 'text-stone-300 hover:bg-white/10' }}">Stok Barang Saya</a>
                    <a href="{{ route('profile') }}" class="block px-3 py-2.5 rounded-lg text-sm font-semibold {{ request()->routeIs('profile') ? 'bg-teal-400 text-ink' : 'text-stone-300 hover:bg-white/10' }}">Profil Saya</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-semibold text-rose-300 hover:bg-white/10">Keluar (Logout)</button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 max-w-7xl w-full mx-auto">
                @if (session()->has('success'))
                    <div class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-teal-50 border-l-4 border-teal-500 text-teal-900 shadow-sm">
                        <svg class="w-5 h-5 text-teal-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-rose-50 border-l-4 border-rose-500 text-rose-900 shadow-sm">
                        <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        <span class="text-sm font-semibold">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content', $slot ?? '')
            </main>

            <footer class="bg-ink text-stone-400 py-4 text-center text-xs">&copy; 2026 Delcom &middot; StokKu Gudang</footer>
        </div>
    @else
        <!-- Guest Layout: split screen -->
        <div class="min-h-screen grid lg:grid-cols-2">
            <aside class="hidden lg:flex flex-col justify-between bg-ink text-white p-12 relative overflow-hidden">
                <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-teal-400/20 blur-3xl"></div>
                <div class="absolute -left-16 bottom-0 w-80 h-80 rounded-full bg-amber-400/10 blur-3xl"></div>
                <a href="{{ route('login') }}" class="relative flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-lg bg-teal-400 text-ink flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-6 9 6v11a1 1 0 01-1 1H4a1 1 0 01-1-1V9zm6 12v-7h6v7" /></svg>
                    </span>
                    <span class="font-extrabold text-xl tracking-tight">Stok<span class="text-teal-300">Ku</span></span>
                </a>
                <div class="relative">
                    <h1 class="text-4xl xl:text-5xl font-extrabold leading-tight tracking-tight">Stok rapi,<br><span class="text-teal-300">gudang tenang.</span></h1>
                    <p class="mt-5 text-stone-300 max-w-md leading-relaxed">Pantau jumlah barang, ambang stok minimum, dan status ketersediaan dalam satu dasbor. Import, export, dan generate item dengan AI.</p>
                    <div class="mt-8 flex gap-3 text-xs font-bold">
                        <span class="px-3 py-1.5 rounded-full bg-teal-400/15 text-teal-300 ring-1 ring-teal-400/30">&#9679; Tersedia</span>
                        <span class="px-3 py-1.5 rounded-full bg-amber-400/15 text-amber-300 ring-1 ring-amber-400/30">&#9679; Stok Menipis</span>
                        <span class="px-3 py-1.5 rounded-full bg-rose-400/15 text-rose-300 ring-1 ring-rose-400/30">&#9679; Habis</span>
                    </div>
                </div>
                <p class="relative text-xs text-stone-500">&copy; 2026 Delcom. PABWE Praktikum 7.</p>
            </aside>

            <main class="flex flex-col justify-center px-4 sm:px-10 py-10 bg-slate-50">
                <a href="{{ route('login') }}" class="lg:hidden flex items-center gap-2 mb-8 mx-auto font-extrabold text-xl text-ink">
                    <span class="w-9 h-9 rounded-lg bg-teal-500 text-white flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-6 9 6v11a1 1 0 01-1 1H4a1 1 0 01-1-1V9zm6 12v-7h6v7" /></svg>
                    </span>
                    Stok<span class="text-teal-600">Ku</span>
                </a>
                @yield('content', $slot ?? '')
            </main>
        </div>
    @endauth

    @unless (request()->routeIs('home', 'login', 'register'))
        @livewireScripts
    @endunless
</body>
</html>
