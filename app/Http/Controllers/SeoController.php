<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = Product::where('is_active', true)->get()->map(fn ($p) => '<url><loc>'.e(route('products.show', $p)).'</loc><lastmod>'.$p->updated_at->toAtomString().'</lastmod></url>')->implode('');

        return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$urls.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        if (config('app.staging_protected')) {
            return response("User-agent: *\nDisallow: /\n", 200, ['Content-Type' => 'text/plain']);
        }

        return response("User-agent: *\nDisallow: /admin\nDisallow: /*?*\nSitemap: ".url('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
