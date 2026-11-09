<div>
    <!-- Top Navigation Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('stocks.index') }}" class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition" title="Kembali ke Daftar Stok">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 truncate max-w-md sm:max-w-xl">{{ $stock->name }}</h1>
                    @if ($stock->sku)
                        <span class="px-2.5 py-0.5 text-xs font-mono bg-slate-100 text-slate-600 rounded border border-slate-200">
                            {{ $stock->sku }}
                        </span>
                    @endif
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)?->badgeClass() ?? 'bg-slate-100 text-slate-700' }}">
                        {{ \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)?->label() ?? ucfirst($stock->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Dibuat pada {{ $stock->created_at ? $stock->created_at->format('d M Y H:i') : '-' }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Quantity Stepper in Detail -->
            <div class="inline-flex items-center space-x-1 bg-white border border-slate-200 rounded-lg p-1 shadow-sm">
                <button type="button" wire:click="adjustQuantity(-1)" wire:loading.attr="disabled"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 font-bold text-sm flex items-center justify-center transition disabled:opacity-50"
                    title="Kurangi Stok (-1)">
                    &minus;
                </button>
                <span class="px-2.5 text-xs font-bold text-slate-800">{{ $stock->quantity }} {{ $stock->unit }}</span>
                <button type="button" wire:click="adjustQuantity(1)" wire:loading.attr="disabled"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-600 font-bold text-sm flex items-center justify-center transition disabled:opacity-50"
                    title="Tambah Stok (+1)">
                    +
                </button>
            </div>

            <!-- Edit Stock Button (Opens Edit Modal) -->
            <button wire:click="openEditModal" wire:loading.attr="disabled" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-blue-700 hover:bg-blue-800 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg wire:loading.remove wire:target="openEditModal" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <svg wire:loading wire:target="openEditModal" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Edit Barang</span>
            </button>

            <!-- Delete Button -->
            <button wire:click="confirmDeleteStock" wire:loading.attr="disabled" class="inline-flex items-center space-x-1 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-lg transition disabled:opacity-75">
                <svg wire:loading.remove wire:target="confirmDeleteStock" class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <svg wire:loading wire:target="confirmDeleteStock" class="animate-spin w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Hapus</span>
            </button>
        </div>
    </div>

    <!-- Cover Stock Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
        <input type="file" wire:model="coverPhoto" id="coverPhotoInput" accept="image/*" class="hidden">

        @if ($stock->cover_image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($stock->cover_image_path))
            <div class="relative group h-64 sm:h-80 w-full bg-slate-900 overflow-hidden">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($stock->cover_image_path) }}" alt="Foto Sampul Barang" class="w-full h-full object-cover">
                
                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-3">
                    <button type="button" onclick="document.getElementById('coverPhotoInput').click()" class="px-4 py-2 bg-white text-slate-800 text-xs font-semibold rounded-xl shadow-lg hover:bg-slate-50 transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Ganti Foto Sampul</span>
                    </button>
                    <button type="button" wire:click="confirmDeleteCover" wire:loading.attr="disabled" class="px-4 py-2 bg-rose-600 text-white text-xs font-semibold rounded-xl shadow-lg hover:bg-rose-700 disabled:opacity-75 transition flex items-center space-x-1.5">
                        <svg wire:loading.remove wire:target="confirmDeleteCover" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <svg wire:loading wire:target="confirmDeleteCover" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Hapus Sampul</span>
                    </button>
                </div>
            </div>
        @else
            <div class="p-8 text-center bg-gradient-to-b from-slate-50 to-white flex flex-col items-center justify-center border-b border-slate-100">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 mb-3 shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-slate-800">Tambahkan Foto Sampul untuk Barang Ini</h2>
                <p class="text-xs text-slate-600 mt-1 max-w-sm">Mendukung format gambar PNG, JPG, GIF hingga ukuran 5MB untuk visualisasi fisik barang.</p>
                
                <button type="button" onclick="document.getElementById('coverPhotoInput').click()" class="mt-4 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Upload Foto Sampul</span>
                </button>
            </div>
        @endif

        <div wire:loading wire:target="coverPhoto" class="p-3 bg-blue-50 border-t border-blue-100 text-center text-xs text-blue-700 font-semibold animate-pulse">
            Sedang mengunggah dan memproses foto sampul...
        </div>
        @error('coverPhoto')
            <div class="p-3 bg-rose-50 border-t border-rose-100 text-center text-xs text-rose-600 font-medium">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- Stock View Mode Details -->
    <div class="space-y-6">
        <!-- Metadata Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider block mb-1">Status Stok</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)?->badgeClass() ?? 'bg-slate-100 text-slate-700' }}">
                    {{ \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)?->label() ?? ucfirst($stock->status) }}
                </span>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider block mb-1">Jumlah & Satuan</span>
                <div class="text-sm font-bold text-slate-900">
                    {{ $stock->quantity }} {{ $stock->unit }}
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider block mb-1">Batas Minimum</span>
                <div class="text-sm font-semibold text-slate-800">
                    {{ $stock->min_stock }} {{ $stock->unit }}
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider block mb-1">Harga Satuan</span>
                <div class="text-sm font-semibold text-slate-800">
                    {{ $stock->price !== null ? 'Rp ' . number_format((float) $stock->price, 0, ',', '.') : 'Tidak ditentukan' }}
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider block mb-1">Terakhir Diperbarui</span>
                <div class="text-sm font-semibold text-slate-800">
                    {{ $stock->updated_at ? $stock->updated_at->format('d M Y H:i') : '-' }}
                </div>
            </div>
        </div>

        <!-- Description Viewer Section -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Deskripsi & Spesifikasi Barang</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Diformat dengan Toast UI Editor / Markdown</p>
                </div>
                <button wire:click="openEditModal" class="inline-flex items-center space-x-1 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:text-blue-800 hover:bg-blue-50 border border-blue-200 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Ubah Deskripsi</span>
                </button>
            </div>

            @if (!empty($stock->description))
                <div class="markdown-content text-slate-800">
                    {!! \Illuminate\Support\Str::markdown($stock->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                </div>
            @else
                <div class="py-12 text-center text-slate-600">
                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-sm font-medium text-slate-600">Belum ada deskripsi untuk barang ini</p>
                    <p class="text-xs text-slate-600 mt-1">Tambahkan catatan spesifikasi atau lokasi penyimpanan dengan Toast UI Editor.</p>
                    <button wire:click="openEditModal" class="mt-4 px-3.5 py-1.5 text-xs font-semibold text-blue-700 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                        + Tambahkan Deskripsi Sekarang
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Stock Modal with Toast UI Editor -->
    @if ($showEditModal)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-blue-600">
                    <div>
                        <h3 class="text-lg font-bold text-white">Ubah Data Stok Barang</h3>
                        <p class="text-xs text-blue-100">Edit nama, SKU, jumlah, satuan, harga, dan deskripsi barang</p>
                    </div>
                    <button type="button" @click="show = false; $wire.closeEditModal()" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveStock" class="flex flex-col flex-1 min-h-0">
                    <div class="space-y-5 px-6 py-5 overflow-y-auto flex-1">
                        <!-- Nama & SKU -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-1">Nama Barang</label>
                                <input wire:model="name" type="text" class="w-full px-4 py-2 text-sm font-medium rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan nama barang..." required>
                                @error('name') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-1">Kode SKU</label>
                                <input wire:model="sku" type="text" class="w-full px-4 py-2 text-sm font-mono rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Opsional">
                                @error('sku') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Quantity, Unit, Min Stock, Price -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-1">Jumlah</label>
                                <input wire:model="quantity" type="number" min="0" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('quantity') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-1">Satuan</label>
                                <input wire:model="unit" type="text" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('unit') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-1">Min. Stok</label>
                                <input wire:model="minStock" type="number" min="0" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('minStock') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-800 mb-1">Harga (Rp)</label>
                                <input wire:model="price" type="number" min="0" step="0.01" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('price') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Deskripsi Barang with Toast UI Editor -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-semibold text-slate-800">Deskripsi Barang (Toast UI Editor)</label>
                                <span class="text-xs text-slate-600">Markdown & WYSIWYG</span>
                            </div>

                            <div wire:ignore
                                 x-data="{
                                     desc: @entangle('description'),
                                     editor: null,
                                     loadingEditor: true,
                                     editorError: false,
                                     async init() {
                                         try {
                                             const Editor = await window.loadToastUIEditor();
                                             if (!this.$refs.detailEditorContainer?.isConnected) {
                                                 return;
                                             }

                                             this.editor = new Editor({
                                                 el: this.$refs.detailEditorContainer,
                                                 height: '300px',
                                                 initialEditType: 'wysiwyg',
                                                 previewStyle: 'vertical',
                                                 initialValue: this.desc || '',
                                                 placeholder: 'Tuliskan rincian spesifikasi barang dalam format teks atau markdown...',
                                                 events: {
                                                     change: () => {
                                                         this.desc = this.editor.getMarkdown();
                                                     }
                                                 }
                                             });

                                             this.$watch('desc', value => {
                                                 if (this.editor && value !== this.editor.getMarkdown()) {
                                                     this.editor.setMarkdown(value || '', false);
                                                 }
                                             });
                                         } catch (error) {
                                             console.error(error);
                                             this.editorError = true;
                                         } finally {
                                             this.loadingEditor = false;
                                         }
                                     }
                                 }"
                                 class="rounded-xl border border-slate-300 overflow-hidden bg-white">
                                <p x-show="loadingEditor" class="p-4 text-sm text-slate-500">Memuat editor deskripsi...</p>
                                <p x-show="editorError" role="alert" class="p-4 text-sm text-rose-700">Editor gagal dimuat. Muat ulang halaman dan coba lagi.</p>
                                <div x-ref="detailEditorContainer"></div>
                            </div>
                            @error('description') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-blue-50 border-t border-blue-100">
                        <button type="button" @click="show = false; $wire.closeEditModal()" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-6 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-75 disabled:cursor-not-allowed rounded-xl shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="saveStock" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveStock">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveStock">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus Stock -->
    @if ($confirmingStockDeletion)
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
                        <h3 class="text-base font-bold text-white">Konfirmasi Hapus Barang</h3>
                        <p class="text-xs text-rose-100">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600">
                        Apakah Anda yakin ingin menghapus stok barang ini secara permanen?
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-rose-50 border-t border-rose-100">
                    <button type="button" @click="show = false; $wire.cancelDeleteStock()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteStock" wire:loading.attr="disabled" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <svg wire:loading wire:target="deleteStock" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="deleteStock">Ya, Hapus Barang</span>
                        <span wire:loading wire:target="deleteStock">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus Cover Photo -->
    @if ($confirmingCoverDeletion)
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
                        <h3 class="text-base font-bold text-white">Konfirmasi Hapus Foto Sampul</h3>
                        <p class="text-xs text-rose-100">Foto sampul akan dihapus dari barang ini</p>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600">
                        Apakah Anda yakin ingin menghapus foto sampul dari barang ini?
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-rose-50 border-t border-rose-100">
                    <button type="button" @click="show = false; $wire.cancelDeleteCover()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteCoverPhoto" wire:loading.attr="disabled" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <svg wire:loading wire:target="deleteCoverPhoto" class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="deleteCoverPhoto">Ya, Hapus Sampul</span>
                        <span wire:loading wire:target="deleteCoverPhoto">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
