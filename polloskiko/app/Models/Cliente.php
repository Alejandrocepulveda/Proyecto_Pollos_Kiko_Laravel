<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $fillable = ['nombre', 'telefono', 'direccion'];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }
}