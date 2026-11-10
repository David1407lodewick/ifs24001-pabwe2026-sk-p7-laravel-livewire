
<?php

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Profile\Livewire\Profile;
use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Livewire\StockList;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CachedStylesheetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/assets/css/{filename}', CachedStylesheetController::class)
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('assets.css');

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.users')
            : redirect()->route('stocks.index');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware(['throttle:1000-per-second'])->group(function () {

    // Halaman login dan register selalu dapat diakses publik
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    // Proses autentikasi hanya untuk pengguna yang belum login
    Route::middleware('guest')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->name('login.authenticate');

        Route::post('/register', [AuthController::class, 'register'])
            ->name('register.store');
    });

    // Halaman yang membutuhkan autentikasi
    Route::middleware('auth')->group(function () {

        Route::post('/logout', function (Request $request) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        })->name('logout');

        Route::get('/stocks', StockList::class)
            ->middleware('owns.stock')
            ->name('stocks.index');

        Route::get('/stocks/{stock}', StockDetail::class)
            ->middleware('owns.stock')
            ->name('stocks.show');

        Route::get('/profile', Profile::class)
            ->name('profile');

        Route::prefix('admin')
            ->name('admin.')
            ->middleware('role:admin')
            ->group(function () {
                Route::get('/users', UserList::class)
                    ->name('users');

                Route::get('/users/{user}/stocks', AdminUserStockList::class)
                    ->name('users.stocks');
            });
    });
});
