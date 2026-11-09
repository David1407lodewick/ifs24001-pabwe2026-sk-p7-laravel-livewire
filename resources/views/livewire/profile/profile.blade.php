<div>
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css"></noscript>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Pengaturan Profil</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola identitas diri, foto profil 1:1, dan keamanan akun Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kiri: Foto Profil 1:1 -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center text-center">
            <h2 class="text-base font-bold text-slate-900 mb-4 self-start">Foto Profil (1:1)</h2>

            <div class="relative w-36 h-36 rounded-2xl overflow-hidden border-2 border-slate-200 shadow-inner mb-4 group bg-slate-100 flex items-center justify-center">
                @if ($user->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo_path))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold text-3xl">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
            </div>

            <div class="w-full space-y-2">
                <input type="file" wire:model="photo" id="profilePhotoInput" accept="image/*" class="hidden">
                
                <button type="button" onclick="document.getElementById('profilePhotoInput').click()" class="w-full py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Upload Foto Baru</span>
                </button>

                <div wire:loading wire:target="photo" class="text-center py-1">
                    <span class="inline-flex items-center text-xs text-blue-600 font-semibold space-x-1.5 animate-pulse">
                        <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Mengunggah file foto...</span>
                    </span>
                </div>

                @if ($user->profile_photo_path)
                    <button type="button" wire:click="confirmDeletePhoto" wire:loading.attr="disabled" class="w-full py-2 px-4 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-lg border border-rose-200 transition flex items-center justify-center space-x-2 disabled:opacity-75">
                        <svg wire:loading.remove wire:target="confirmDeletePhoto" class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <svg wire:loading wire:target="confirmDeletePhoto" class="animate-spin w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Hapus Foto Profil</span>
                    </button>
                @endif
            </div>

            <p class="text-xs text-slate-600 mt-4">
                Mendukung PNG, JPG, GIF hingga 5MB. Foto otomatis dipotong menjadi rasio presisi 1:1.
            </p>
        </div>

        <!-- Kolom Tengah & Kanan: Identitas Diri & Ubah Password -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Form Identitas Diri -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Ubah Identitas Diri</h2>

                <form wire:submit.prevent="updateProfileInformation" class="space-y-4">
                    <div>
                        <label for="profile-name" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                        <input id="profile-name" wire:model="name" type="text" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('name') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="profile-username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                            <input id="profile-username" wire:model="username" type="text" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono" required>
                            @error('username') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="profile-email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input id="profile-email" wire:model="email" type="email" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('email') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 disabled:opacity-75 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="updateProfileInformation" class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="updateProfileInformation">Simpan Perubahan Identitas</span>
                            <span wire:loading wire:target="updateProfileInformation">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Form Ubah Password -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Ubah Password Akun</h2>

                <form wire:submit.prevent="updatePassword" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password Saat Ini</label>
                        <input wire:model="current_password" type="password" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Password saat ini" required>
                        @error('current_password') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                            <input wire:model="password" type="password" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Minimal 8 karakter" required>
                            @error('password') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password Baru</label>
                            <input wire:model="password_confirmation" type="password" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ulangi password baru" required>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 disabled:opacity-75 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="updatePassword" class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="updatePassword">Perbarui Password</span>
                            <span wire:loading wire:target="updatePassword">Memperbarui...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Crop Foto Profil -->
    @if ($showCropModal && $photo)
        <div class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-slate-900/50 backdrop-blur-sm"
             wire:ignore.self
             x-data="{
                cropper: null,
                initCropper() {
                    const img = document.getElementById('imageToCrop');
                    if (!img) return;
                    if (this.cropper) {
                        this.cropper.destroy();
                    }
                    this.cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 0.9,
                        responsive: true,
                        background: false
                    });
                },
                applyCrop() {
                    if (this.cropper) {
                        let canvas = this.cropper.getCroppedCanvas({
                            width: 400,
                            height: 400
                        });
                        @this.set('croppedPhoto', canvas.toDataURL('image/png'));
                        @this.saveCroppedPhoto();
                    }
                }
             }"
             x-init="$nextTick(() => { initCropper(); })">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-blue-600">
                    <h3 class="text-base font-bold text-white flex items-center space-x-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Sesuaikan Potongan Foto (Rasio 1:1)</span>
                    </h3>
                    <button wire:click="closeCropModal" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <div class="max-h-80 overflow-hidden flex items-center justify-center bg-slate-900 rounded-xl">
                        <img id="imageToCrop" src="{{ $photo->temporaryUrl() }}" alt="Preview" class="max-w-full block">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-blue-50 border-t border-blue-100">
                    <button type="button" wire:click="closeCropModal" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" @click="applyCrop()" wire:loading.attr="disabled" class="px-4 py-2 text-xs font-semibold text-white bg-blue-700 hover:bg-blue-800 disabled:opacity-75 disabled:cursor-not-allowed rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <svg wire:loading wire:target="saveCroppedPhoto" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveCroppedPhoto">Terapkan & Simpan Foto</span>
                        <span wire:loading wire:target="saveCroppedPhoto">Menyimpan Foto...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus Foto Profil -->
    @if ($confirmingPhotoDeletion)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-center space-x-3 px-6 py-4 bg-rose-600">
                    <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Konfirmasi Hapus Foto Profil</h3>
                        <p class="text-xs text-rose-100">Foto akan dihapus dan kembali ke avatar default</p>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600">
                        Apakah Anda yakin ingin menghapus foto profil ini?
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-rose-50 border-t border-rose-100">
                    <button type="button" @click="show = false; $wire.cancelDeletePhoto()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" wire:click="deletePhoto" wire:loading.attr="disabled" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <svg wire:loading wire:target="deletePhoto" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="deletePhoto">Ya, Hapus Foto</span>
                        <span wire:loading wire:target="deletePhoto">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
