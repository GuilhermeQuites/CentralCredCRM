<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefinancingNotification extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pendente';
    public const STATUS_VIEWED = 'visualizado';
    public const STATUS_NOT_REFINANCED = 'nao_refinanciado';

    protected $fillable = [
        'contract_id',
        'status',
        'viewed_at',
        'notify_after_paid_installments',
        'marked_not_refinanced_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            'marked_not_refinanced_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
