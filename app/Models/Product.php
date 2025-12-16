<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'precio',
        'impuesto',
        'impuesto_calculado',
        'existencias',
    ];

    /**
     * Get the providers for the product.
     */
    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class);
    }
}
