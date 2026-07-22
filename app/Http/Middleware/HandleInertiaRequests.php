<?php

namespace App\Http\Middleware;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'cartCount' => collect($request->session()->get('cart', []))->sum('quantity'),
            'flash' => ['success' => fn () => $request->session()->get('success')],
            'catalogMenu' => fn () => Category::whereNull('parent_id')->where('is_active', true)
                ->with(['children' => fn ($query) => $query->where('is_active', true)])
                ->get(['id', 'parent_id', 'name', 'slug']),
        ];
    }
}
