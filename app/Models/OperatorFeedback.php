<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modello per feedback operatori
 * 
 * Questo modello gestisce le richieste operative degli operatori che vengono
 * lette esternamente tramite Cursor per suggerire funzionalità e miglioramenti.
 * Le richieste vengono elaborate tramite API REST.
 */
class OperatorFeedback extends Model
{
    protected $table = 'operator_feedback';

    protected $fillable = [
        'titolo',
        'descrizione',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Scope per richieste in attesa
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope per richieste in corso
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope per richieste completate
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope per richieste rifiutate
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope per status specifico
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
