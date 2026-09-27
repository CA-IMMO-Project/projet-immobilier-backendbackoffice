<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Journal d'audit append-only : ne jamais mettre à jour ni supprimer une ligne
 * existante depuis le code applicatif (seule la création est autorisée).
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    const UPDATED_AT = null; // pas de colonne updated_at

    protected $fillable = [
        'user_id', 'user_name', 'action', 'entity_type', 'entity_id',
        'entity_label', 'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Empêche toute modification ou suppression depuis Eloquent : le journal
     * doit rester append-only. Utilisez DB::table('audit_logs')->insert(...)
     * ou la création via ce modèle uniquement.
     */
    protected static function booted(): void
    {
        static::updating(fn () => throw new \RuntimeException('audit_logs est append-only : la mise à jour est interdite.'));
        static::deleting(fn () => throw new \RuntimeException('audit_logs est append-only : la suppression est interdite.'));
    }
}
