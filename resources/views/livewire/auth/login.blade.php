@extends('components.layouts.app')

@section('content')
<div class="w-full max-w-md mx-auto">
    <div class="mb-8">
        <span class="inline-block px-2.5 py-1 mb-3 rounded bg-teal-100 text-teal-800 text-[11px] font-bold uppercase tracking-widest">Selamat datang kembali</span>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke Akun Anda</h1>
        <p class="text-sm text-slate-500 mt-2">Kelola inventaris stok barang Anda secara teratur dan akurat</p>
    </div>

    <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-5">
        @csrf
        <div>
            <label for="login-username" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Username atau Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </span>
                <input name="login" value="{{ old('login') }}" type="text" id="login-username" class="w-full pl-10 pr-3.5 py-3 rounded-lg border-2 border-slate-200 bg-white focus:outline-none focus:border-teal-500 text-sm transition" placeholder="username atau nama@email.com" autocomplete="username" required>
            </div>
            @error('login') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="login-password" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </span>
                <input name="password" type="password" id="login-password" class="w-full pl-10 pr-3.5 py-3 rounded-lg border-2 border-slate-200 bg-white focus:outline-none focus:border-teal-500 text-sm transition" placeholder="••••••••" autocomplete="current-password" required>
            </div>
            @error('password') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
            <input name="remember" value="1" type="checkbox" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" @checked(old('remember'))>
            <span>Ingat Saya</span>
        </label>

        <button type="submit" id="login-submit" class="w-full py-3 px-4 bg-ink hover:bg-teal-700 text-white font-bold rounded-lg shadow-lg shadow-black/10 transition flex items-center justify-center gap-2">
            <span>Masuk</span>
        </button>
    </form>

    <p class="mt-8 text-sm text-slate-500">
        Belum memiliki akun?
        <a href="{{ route('register') }}" class="font-bold text-teal-700 hover:text-teal-800 underline underline-offset-4">Daftar sekarang</a>
    </p>
</div>
@endsection
