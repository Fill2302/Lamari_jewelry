<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationCredential extends Model
{
    protected $guarded = [];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'is_active' => 'boolean',
            'package_weight' => 'decimal:2',
        ];
    }
}
