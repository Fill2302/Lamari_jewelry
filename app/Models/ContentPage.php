<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['show_in_menu' => 'boolean', 'is_active' => 'boolean'];
    }
}
