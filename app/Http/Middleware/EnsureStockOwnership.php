<?php

namespace App\Http\Middleware;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStockOwnership
{
    /**
     * Handle an incoming request ensuring members do not access other users' data.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = $request->user();

        if (!$currentUser) {
            abort(401, 'Unauthenticated');
        }

        // Check if route has {user} parameter
        $routeUser = $request->route('user');
        if ($routeUser) {
            $targetUserId = $routeUser instanceof User ? $routeUser->id : (int) $routeUser;

            if ($currentUser->isMember() && $currentUser->id !== $targetUserId) {
                abort(403, 'Akses ditolak: Member tidak dapat mengakses stok barang orang lain.');
            }
        }

        // Check if route has {stock} parameter
        $routeStock = $request->route('stock');
        if ($routeStock) {
            $stock = $routeStock instanceof Stock ? $routeStock : Stock::find($routeStock);

            if ($stock) {
                // If member, must strictly own the stock
                if ($currentUser->isMember() && $currentUser->id !== $stock->user_id) {
                    abort(403, 'Akses ditolak: Member tidak dapat mengakses stok barang orang lain.');
                }

                // If write method (PUT, PATCH, DELETE), even admin cannot modify other users' stocks
                if ($request->isMethodSafe() === false && $currentUser->id !== $stock->user_id) {
                    abort(403, 'Akses ditolak: Hanya pemilik stok barang yang dapat mengubah data ini.');
                }
            }
        }

        return $next($request);
    }
}

