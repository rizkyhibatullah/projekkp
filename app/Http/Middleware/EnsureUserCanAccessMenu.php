<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EnsureUserCanAccessMenu
{
    public function handle(Request $request, Closure $next)
    {

        if ($request->is('api/*')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $routeName = $request->route()->getName();


        // Jika route tidak memiliki nama atau route khusus yang boleh diakses
        if (is_null($routeName)) {
            return $next($request);
        }

        // Daftar route yang boleh diakses tanpa pengecekan
        $allowedRoutes = ['home', 'profile', 'logout'];
        if (in_array($routeName, $allowedRoutes)) {
            return $next($request);
        }

        // Konversi route name ke menu slug
        $menuSlug = $this->convertRouteToMenuSlug($routeName);

        // Jika user tidak memiliki akses ke menu tersebut
        if (!Gate::allows('access_menu', $menuSlug)) {
            // Jika request adalah AJAX, kembalikan JSON error
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Anda tidak memiliki izin untuk mengakses data ini.',
                    'message' => 'Unauthorized access'
                ], 403);
            }

            return redirect('/home')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }

    /**
     * Convert route name to menu slug
     */
    protected function convertRouteToMenuSlug(string $routeName): string
    {
        // Mapping khusus untuk route yang tidak mengikuti konvensi
        $specialMappings = [
            'home' => 'dashboard',
            'profile' => 'profile',
            'rekap.generate' => 'absensi',
            'inventory.stock_report' => 'warehouse',
            'transfergudang.inTransit' => 'transfergudang',
        ];

        if (array_key_exists($routeName, $specialMappings)) {
            return $specialMappings[$routeName];
        }

        $parts = explode('.', $routeName);
        $action = end($parts);

        $standardActions = [
            // CRUD Standar (dan variasinya)
            'index',
            'store',
            'create',
            'show',
            'edit',
            'update',
            'destroy',
            'destroyHeader',
            'destroyDetail',
            'cancel',

            // Aksi Data JSON / Helper
            'json',
            'pdf',
            'data',
            'details',
            'fetch',
            'generate',
            'check',
            'customers',
            'suppliers',
            'warehouses',
            'product-data',
            'uom-options',
            'getByDivision',
            'getSubclasses',
            'getNamaPerkiraan',
            'getNextNo',
            'searchEmployees',
            'getRoleMenusByRoleId',

            // Aksi Dokumen Kustom
            'updateHeader',
            'storeDetail',
            'updateDetail',
            'publish',
            'publishEdit',
            'updateMenuAccess',
            'submit',
            'fetchDetails',
            'syncDetails',
            'getTransferDetails',

            // Aksi Approval
            'approve',
            'approveAll',
            'reject',

            // Aksi Printing
            'print',
            'printAll',
            'getProducts',

            'inTransit'
        ];

        if (count($parts) > 1 && in_array($action, $standardActions)) {
            // Jika route memiliki aksi standar, kembalikan bagian dasar
            $baseRoute = implode('.', array_slice($parts, 0, -1));

            // Handling khusus untuk details
            if (strpos($baseRoute, '.details') !== false) {
                $baseRoute = str_replace('.details', '', $baseRoute);
            }

            return $baseRoute;
        }

        // Default: gunakan seluruh route name sebagai slug
        return $routeName;
    }
}
