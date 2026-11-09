<div>
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8 pb-6 border-b-2 border-slate-200">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-teal-600">Inventaris</span>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 mt-1">Daftar Stok Barang Saya</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola ketersediaan barang, jumlah inventaris, dan ambang batas minimum</p>
        </div>

        <div class="relative" x-data="{ open: false, loading: null }" @click.outside="open = false" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" :disabled="!!loading"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 shadow-sm transition disabled:opacity-60"
                title="Menu aksi">
                <svg x-show="!loading" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                </svg>
                <svg x-show="loading" x-cloak class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </button>

            <div x-show="open" x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 z-30 mt-2 w-56 origin-top-right rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl shadow-slate-200/70">
                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Aksi Cepat</div>

                <button type="button" :disabled="!!loading"
                    @click="
                        loading = 'create'; open = false;
                        $wire.openCreateModal().finally(() => loading = null);
                    "
                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-50 transition disabled:opacity-60">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <svg x-show="loading !== 'create'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <svg x-show="loading === 'create'" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </span>
                    Tambah Stok Barang
                </button>

                <button type="button" :disabled="!!loading"
                    @click="
                        loading = 'ai'; open = false;
                        $wire.openAiModal().finally(() => loading = null);
                    "
                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-sm font-semibold text-purple-700 hover:bg-purple-50 transition disabled:opacity-60">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                        <svg x-show="loading !== 'ai'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        <svg x-show="loading === 'ai'" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </span>
                    Generate dengan AI
                </button>

                <div class="my-1.5 border-t border-slate-100"></div>
                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Spreadsheet</div>

                <button type="button" :disabled="!!loading"
                    @click="
                        loading = 'import'; open = false;
                        $wire.openImportModal().finally(() => loading = null);
                    "
                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50 transition disabled:opacity-60">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <svg x-show="loading !== 'import'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        <svg x-show="loading === 'import'" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </span>
                    Import Excel
                </button>

                <button type="button" :disabled="!!loading"
                    @click="
                        loading = 'export'; open = false;
                        $wire.exportExcel().finally(() => loading = null);
                    "
                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition disabled:opacity-60">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <svg x-show="loading !== 'export'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        <svg x-show="loading === 'export'" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </span>
                    <span x-text="loading === 'export' ? 'Mengekspor...' : 'Export Excel'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div
        class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="w-full sm:w-72 relative">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, SKU, atau deskripsi..."
                class="w-full pl-3.5 pr-9 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div wire:loading wire:target="search,statusFilter" class="absolute right-3 top-2.5">
                <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>
        </div>

        <div class="flex items-center space-x-3 w-full sm:w-auto">
            <label for="stock-status-filter" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status:</label>
            <select id="stock-status-filter" wire:model.live="statusFilter"
                class="px-3 py-2 text-sm rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Semua Status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Stock List Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        @if ($stocks->isEmpty())
            <div class="py-16 text-center">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p class="text-base font-semibold text-slate-700">Belum ada stok barang ditemukan</p>
                <p class="text-sm text-slate-500 mt-1">Tambah stok barang baru atau generate otomatis menggunakan AI</p>
            </div>
        @else
            <ul class="grid gap-3 p-3 sm:p-4 bg-slate-50">
                @foreach ($stocks as $stock)
                    <li wire:key="stock-{{ $stock->id }}" class="relative bg-white rounded-xl border border-slate-200 border-l-4 {{ match (\App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)) { \App\Features\Stock\Enums\StockStatus::AVAILABLE => 'border-l-teal-500', \App\Features\Stock\Enums\StockStatus::LOW_STOCK => 'border-l-amber-400', \App\Features\Stock\Enums\StockStatus::OUT_OF_STOCK => 'border-l-rose-500', default => 'border-l-slate-300' } }} p-4 sm:p-5 hover:shadow-md transition flex items-center justify-between gap-4">
                        <div class="flex items-center space-x-4 flex-1 min-w-0">
                            <!-- Quantity Stepper (+ / -) -->
                            <div class="flex items-center space-x-1.5 bg-ink rounded-xl p-1 flex-shrink-0">
                                <button type="button"
                                    wire:click="adjustQuantity({{ $stock->id }}, -1)"
                                    wire:loading.attr="disabled"
                                    class="w-7 h-7 rounded-lg bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-600 font-bold text-sm shadow-sm flex items-center justify-center transition disabled:opacity-50"
                                    title="Kurangi Stok (-1)">
                                    &minus;
                                </button>
                                <div class="px-2 text-center min-w-[3.5rem]">
                                    <span class="text-sm font-bold text-white">{{ $stock->quantity }}</span>
                                    <span class="text-[10px] text-stone-400 block leading-none">{{ $stock->unit }}</span>
                                </div>
                                <button type="button"
                                    wire:click="adjustQuantity({{ $stock->id }}, 1)"
                                    wire:loading.attr="disabled"
                                    class="w-7 h-7 rounded-lg bg-white hover:bg-emerald-50 text-slate-600 hover:text-emerald-600 font-bold text-sm shadow-sm flex items-center justify-center transition disabled:opacity-50"
                                    title="Tambah Stok (+1)">
                                    +
                                </button>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('stocks.show', $stock->id) }}"
                                        class="text-base font-semibold hover:text-blue-600 transition text-slate-900">
                                        {{ $stock->name }}
                                    </a>
                                    @if ($stock->sku)
                                        <span class="px-2 py-0.5 text-xs font-mono bg-slate-100 text-slate-600 rounded border border-slate-200">
                                            {{ $stock->sku }}
                                        </span>
                                    @endif
                                    <span
                                        class="px-2 py-0.5 text-xs font-semibold rounded-full border {{ \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)?->badgeClass() ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ \App\Features\Stock\Enums\StockStatus::tryFrom($stock->status)?->label() ?? ucfirst($stock->status) }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-slate-500">
                                    <span>Min. Stok: <strong>{{ $stock->min_stock }} {{ $stock->unit }}</strong></span>
                                    @if ($stock->price !== null)
                                        <span>Harga: <strong>Rp {{ number_format((float) $stock->price, 0, ',', '.') }}</strong></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Row Actions Menu -->
                        <div class="relative flex-shrink-0" x-data="{ open: false, loading: null }" @click.outside="open = false" @keydown.escape.window="open = false">
                            <button type="button" @click="open = !open" :disabled="!!loading"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-transparent text-slate-500 hover:border-slate-200 hover:bg-white hover:text-slate-800 transition disabled:opacity-60"
                                title="Tindakan">
                                <svg x-show="!loading" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                                <svg x-show="loading" x-cloak class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 z-20 mt-1.5 w-48 origin-top-right rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl shadow-slate-200/70">
                                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Tindakan</div>

                                <a href="{{ route('stocks.show', $stock->id) }}" @click="open = false"
                                    class="flex w-full items-center gap-2.5 px-3 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-50 transition">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </span>
                                    Lihat Detail
                                </a>

                                <button type="button" :disabled="!!loading"
                                    @click="
                                        loading = 'edit'; open = false;
                                        $wire.openEditModal({{ $stock->id }}).finally(() => loading = null);
                                    "
                                    class="flex w-full items-center gap-2.5 px-3 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50 transition disabled:opacity-60">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                                        <svg x-show="loading !== 'edit'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <svg x-show="loading === 'edit'" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </span>
                                    <span x-text="loading === 'edit' ? 'Membuka...' : 'Edit Stok'"></span>
                                </button>

                                <div class="my-1 border-t border-slate-100"></div>

                                <button type="button" :disabled="!!loading"
                                    @click="
                                        loading = 'delete'; open = false;
                                        $wire.confirmDelete({{ $stock->id }}).finally(() => loading = null);
                                    "
                                    class="flex w-full items-center gap-2.5 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50 transition disabled:opacity-60">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                                        <svg x-show="loading !== 'delete'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <svg x-show="loading === 'delete'" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </span>
                                    <span x-text="loading === 'delete' ? 'Memproses...' : 'Hapus Stok'"></span>
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="p-4 border-t border-slate-100">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Form Modal -->
    @if ($showFormModal)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-xl border border-slate-200 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-blue-600">
                    <h3 class="text-lg font-bold text-white">
                        {{ $editingStockId ? 'Edit Stok Barang' : 'Tambah Stok Barang Baru' }}
                    </h3>
                    <button type="button" @click="show = false; $wire.closeFormModal()" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveStock" class="flex flex-col flex-1 min-h-0">
                    <div class="space-y-4 px-6 py-5 overflow-y-auto flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                                <input wire:model="formName" type="text"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    placeholder="Contoh: Laptop ASUS ExpertBook" required>
                                @error('formName')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kode SKU (Opsional)</label>
                                <input wire:model="formSku" type="text"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                                    placeholder="Contoh: BRG-1001">
                                @error('formSku')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Stok</label>
                                <input wire:model="formQuantity" type="number" min="0"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    required>
                                @error('formQuantity')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Satuan</label>
                                <input wire:model="formUnit" type="text"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    placeholder="pcs, box, unit" required>
                                @error('formUnit')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Min. Stok</label>
                                <input wire:model="formMinStock" type="number" min="0"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    required>
                                @error('formMinStock')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Harga Satuan (Rp)</label>
                                <input wire:model="formPrice" type="number" min="0" step="0.01"
                                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    placeholder="Opsional">
                                @error('formPrice')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-slate-700">Deskripsi Barang (Toast UI Editor)</label>
                                <span class="text-xs text-slate-400">Markdown & WYSIWYG</span>
                            </div>

                            <div wire:ignore
                                 x-data="{
                                     desc: @entangle('formDescription'),
                                     editor: null,
                                     loadingEditor: true,
                                     editorError: false,
                                     async init() {
                                         try {
                                             const Editor = await window.loadToastUIEditor();
                                             if (!this.$refs.formEditorContainer?.isConnected) {
                                                 return;
                                             }

                                             this.editor = new Editor({
                                                 el: this.$refs.formEditorContainer,
                                                 height: '240px',
                                                 initialEditType: 'wysiwyg',
                                                 previewStyle: 'vertical',
                                                 initialValue: this.desc || '',
                                                 placeholder: 'Detail spesifikasi barang dalam format teks atau markdown...',
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
                                    <div x-ref="formEditorContainer"></div>
                            </div>
                            @error('formDescription')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-blue-50 border-t border-blue-100">
                        <button type="button" @click="show = false; $wire.closeFormModal()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-75 disabled:cursor-not-allowed rounded-lg shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="saveStock"
                                class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveStock">Simpan</span>
                            <span wire:loading wire:target="saveStock">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Import Excel Modal -->
    @if ($showImportModal)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-xl border border-slate-200 overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-emerald-600">
                    <h3 class="text-lg font-bold text-white">Import Stok Barang dari File Excel</h3>
                    <button type="button" @click="show = false; $wire.closeImportModal()" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="importExcel" class="flex flex-col">
                    <div class="space-y-4 px-6 py-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pilih File (.xlsx, .xls,
                                .csv)</label>
                            <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                                required>
                            @error('importFile')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600 space-y-1">
                            <p class="font-semibold text-slate-700">Format Kolom Spreadsheet:</p>
                            <p>Kolom A: No | Kolom B: Name | Kolom C: SKU | Kolom D: Description | Kolom E: Quantity | Kolom F: Unit | Kolom G: Price | Kolom H: Min Stock | Kolom I: Status</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-emerald-50 border-t border-emerald-100">
                        <button type="button" @click="show = false; $wire.closeImportModal()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-75 disabled:cursor-not-allowed rounded-lg shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="importExcel,importFile"
                                class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="importExcel,importFile">Upload & Import</span>
                            <span wire:loading wire:target="importExcel,importFile">Mengupload & Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- AI Stock Generator Modal -->
    @if ($showAiModal)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="modal-scroll-lock fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overscroll-contain p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-xl border border-slate-200 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 bg-purple-600">
                    <div class="flex items-center space-x-3">
                        <span class="p-2 bg-white/20 text-white rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-white">Generate Stok Barang Otomatis dengan AI</h3>
                            <p class="text-xs text-purple-100">Minta AI membuatkan daftar stok barang terstruktur sesuai kebutuhan Anda</p>
                        </div>
                    </div>
                    <button type="button" @click="show = false; $wire.closeAiModal()" class="text-white/80 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 overflow-y-auto flex-1 px-6 py-5">
                    @if ($aiErrorMessage)
                        <div
                            class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                            {{ $aiErrorMessage }}
                        </div>
                    @endif

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Permintaan Anda /
                                Prompt</label>
                            <textarea wire:model="aiPrompt" rows="3"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                                placeholder="Contoh: Buatkan daftar stok peralatan jaringan komputer dan laboratorium IT..."></textarea>
                            @error('aiPrompt')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <label class="text-xs font-medium text-slate-600">Jumlah item:</label>
                                    <input wire:model="aiCount" type="number" min="1"
                                        class="w-20 px-2.5 py-1 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 font-medium text-center"
                                        placeholder="3">
                                    <span class="text-xs text-slate-400">item</span>
                                </div>
                                @error('aiCount')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <button wire:click="generateWithAi" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-75 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center justify-center space-x-2">
                                <svg wire:loading wire:target="generateWithAi"
                                    class="animate-spin -ml-0.5 mr-1.5 h-3.5 w-3.5 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="generateWithAi">Generate Sekarang</span>
                                <span wire:loading wire:target="generateWithAi">Sedang memproses AI...</span>
                            </button>
                        </div>
                    </div>

                    <!-- AI In-Progress Waiting Banner -->
                    <div wire:loading wire:target="generateWithAi"
                        class="p-6 bg-purple-50 rounded-xl border border-purple-200 text-center space-y-2">
                        <svg class="animate-spin h-7 w-7 text-purple-600 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <p class="text-sm font-semibold text-purple-900">Menghubungi AI untuk menyusun daftar stok barang...</p>
                        <p class="text-xs text-purple-600">Mohon tunggu beberapa detik, item inventaris sedang digenerate.</p>
                    </div>

                    <!-- AI Preview Results -->
                    @if (!empty($aiGeneratedItems))
                        <div class="pt-4 border-t border-slate-200">
                            <h4 class="text-sm font-bold text-slate-800 mb-2">Pilih Stok Barang yang ingin ditambahkan:</h4>
                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach ($aiGeneratedItems as $index => $item)
                                    <label
                                        class="flex items-start space-x-3 p-3 rounded-lg border border-slate-200 bg-slate-50 hover:bg-purple-50/50 cursor-pointer transition">
                                        <input wire:model="aiSelectedIndices" value="{{ $index }}"
                                            type="checkbox"
                                            class="mt-1 rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                        <div class="flex-1 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-slate-900">{{ $item['name'] }}</span>
                                                <span class="text-slate-500 font-semibold">{{ $item['quantity'] ?? 0 }} {{ $item['unit'] ?? 'pcs' }}</span>
                                            </div>
                                            @if (!empty($item['sku']))
                                                <span class="text-[10px] font-mono text-slate-400">SKU: {{ $item['sku'] }}</span>
                                            @endif
                                            @if (!empty($item['description']))
                                                <p class="text-slate-600 mt-1">{{ $item['description'] }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-purple-50 border-t border-purple-100">
                    <button type="button" @click="show = false; $wire.closeAiModal()"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    @if (!empty($aiGeneratedItems))
                        <button type="button" wire:click="saveAiGenerated" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-75 disabled:cursor-not-allowed rounded-lg shadow-sm transition flex items-center space-x-2">
                            <svg wire:loading wire:target="saveAiGenerated"
                                class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveAiGenerated">Simpan ke Stok Barang Saya</span>
                            <span wire:loading wire:target="saveAiGenerated">Menyimpan...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Stock Confirmation Modal -->
    @if ($confirmingStockDeletionId)
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
                        <h3 class="text-base font-bold text-white">Konfirmasi Hapus Stok Barang</h3>
                        <p class="text-xs text-rose-100">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-slate-600">
                        Apakah Anda yakin ingin menghapus stok barang ini secara permanen dari inventaris Anda?
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-rose-50 border-t border-rose-100">
                    <button type="button" @click="show = false; $wire.cancelDelete()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button"
                        x-data="{ deleting: false }"
                        x-bind:disabled="deleting"
                        @click="
                            if (deleting) return;
                            deleting = true;
                            $wire.deleteStock().finally(() => deleting = false);
                        "
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-75 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                        <svg x-show="deleting" x-cloak class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="deleting ? 'Menghapus...' : 'Ya, Hapus Stok'"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
