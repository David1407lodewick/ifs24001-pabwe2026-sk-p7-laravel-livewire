@extends('components.layouts.app')

@section('content')
<div class="w-full max-w-md mx-auto">
    <div class="mb-8">
        <span class="inline-block px-2.5 py-1 mb-3 rounded bg-amber-100 text-amber-800 text-[11px] font-bold uppercase tracking-widest">Akun baru</span>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Buat Akun Member</h1>
        <p class="text-sm text-slate-500 mt-2">Daftar sekarang untuk mulai mengelola stok barang Anda</p>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Nama Lengkap</label>
            <input name="name" value="{{ old('name') }}" type="text" id="name" class="w-full px-3.5 py-3 rounded-lg border-2 border-slate-200 bg-white focus:outline-none focus:border-teal-500 text-sm transition" placeholder="Nama Anda" autocomplete="name" required>
            @error('name') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Username</label>
            <input name="username" value="{{ old('username') }}" type="text" id="username" class="w-full px-3.5 py-3 rounded-lg border-2 border-slate-200 bg-white focus:outline-none focus:border-teal-500 text-sm transition" placeholder="username_kamu" autocomplete="username" required>
            @error('username') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email</label>
            <input name="email" value="{{ old('email') }}" type="email" id="email" class="w-full px-3.5 py-3 rounded-lg border-2 border-slate-200 bg-white focus:outline-none focus:border-teal-500 text-sm transition" placeholder="nama@email.com" autocomplete="email" required>
            @error('email') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
            <input name="password" type="password" id="password" class="w-full px-3.5 py-3 rounded-lg border-2 border-slate-200 bg-white focus:outline-none focus:border-teal-500 text-sm transition" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
            @error('password') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Konfirmasi Password</label>
            <input name="password_confirmation" type="password" id="password_confirmation" class="w-full px-3.5 py-3 rounded-lg border-2 border-slate-200 bg-white focus:outline-none focus:border-teal-500 text-sm transition" placeholder="Ulangi password" autocomplete="new-password" required>
        </div>

        <button type="submit" class="w-full py-3 px-4 bg-ink hover:bg-teal-700 text-white font-bold rounded-lg shadow-lg shadow-black/10 transition flex items-center justify-center gap-2">
            <span>Daftar Akun</span>
        </button>
    </form>

    <p class="mt-8 text-sm text-slate-500">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-bold text-teal-700 hover:text-teal-800 underline underline-offset-4">Masuk di sini</a>
    </p>
</div>
@endsection
