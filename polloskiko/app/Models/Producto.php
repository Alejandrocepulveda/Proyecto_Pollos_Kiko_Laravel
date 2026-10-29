<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    /** Con este número de unidades o menos se considera "stock bajo" */
    public const STOCK_BAJO = 5;

    protected $fillable = ['nombre', 'stock', 'precio_venta'];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }
}