<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'total',
        'total_impuestos',
        'user_id',
        'cancelada',
        'cancelada_at',
        'cancelada_por',
    ];

    protected $casts = [
        'cancelada' => 'boolean',
        'cancelada_at' => 'datetime',
    ];

    /**
     * Get the sale details for the sale.
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Get the user (vendedor) that made the sale.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin user that cancelled the sale.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelada_por');
    }
}
