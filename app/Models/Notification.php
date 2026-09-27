<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification interne "métier" (table notifications personnalisée), distincte
 * du canal de notification Illuminate\Notifications\DatabaseNotification.
 * Ne pas utiliser le trait Notifiable ni les canaux natifs de Laravel sur ce modèle.
 */
class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    const UPDATED_AT = null; // pas de colonne updated_at

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'priority',
        'entity_type', 'entity_id', 'entity_label', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeNonLues($query)
    {
        return $query->whereNull('read_at');
    }
}
