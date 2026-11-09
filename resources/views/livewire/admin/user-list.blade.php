<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8 pb-6 border-b-2 border-slate-200">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-amber-600">Panel Admin</span>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-1">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar semua pengguna terdaftar dan pantau stok barang mereka</p>
        </div>

        <div class="w-full sm:w-80 relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..." class="w-full pl-10 pr-9 py-2.5 text-sm rounded-lg border-2 border-slate-200 focus:outline-none focus:border-teal-500 bg-white">
            <div wire:loading wire:target="search" class="absolute right-3 top-3">
                <svg class="animate-spin h-4 w-4 text-teal-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>
        </div>
    </div>

    @if ($users->isEmpty())
        <div class="py-16 text-center bg-white rounded-2xl border-2 border-dashed border-slate-300 text-slate-500">
            Tidak ada data pengguna yang ditemukan.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($users as $user)
                <div wire:key="user-{{ $user->id }}" class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition overflow-hidden">
                    <div class="h-1.5 {{ $user->isAdmin() ? 'bg-amber-400' : 'bg-teal-400' }}"></div>
                    <div class="p-5">
                        <div class="flex items-start gap-3">
                            <span class="w-12 h-12 rounded-xl flex items-center justify-center font-extrabold text-sm shrink-0 {{ $user->isAdmin() ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800' }}">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-slate-900 truncate">{{ $user->name }}</div>
                                <div class="text-xs text-slate-400 font-mono truncate">&#64;{{ $user->username }}</div>
                                <div class="text-sm text-slate-600 truncate mt-1">{{ $user->email }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider {{ $user->isAdmin() ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-slate-50 px-3 py-2.5">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jumlah Stok</div>
                                <div class="text-xl font-extrabold text-slate-800">{{ $user->stocks_count }}</div>
                            </div>
                            <div class="rounded-lg bg-slate-50 px-3 py-2.5">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bergabung</div>
                                <div class="text-xs font-semibold text-slate-700 mt-1.5">{{ $user->created_at ? $user->created_at->format('d M Y H:i') : '-' }}</div>
                            </div>
                        </div>

                        <a href="{{ route('admin.users.stocks', $user->id) }}" class="mt-4 flex items-center justify-center gap-1.5 w-full px-3 py-2.5 text-sm font-bold text-white bg-ink hover:bg-teal-700 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Lihat Stok</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif
</div>
