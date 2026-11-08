<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvisoStock extends Model
{
    protected $table = 'avisos_stock';

    protected $fillable = ['producto_id', 'user_id', 'nota', 'atendido_at', 'atendido_por'];

    protected function casts(): array
    {
        return ['atendido_at' => 'datetime'];
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereNull('atendido_at');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }
}
