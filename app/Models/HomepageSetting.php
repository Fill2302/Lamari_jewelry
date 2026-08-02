<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'show_new_products' => 'boolean',
            'show_hit_products' => 'boolean',
            'faq_items' => 'array',
            'instagram_images' => 'array',
        ];
    }
}
