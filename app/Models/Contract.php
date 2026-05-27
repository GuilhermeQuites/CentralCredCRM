<?php

namespace App\Models;

use App\Services\RefinancingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contract extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'finished', 'cancelled'];
    public const TYPES = ['refinancing', 'new', 'portability'];

    protected $fillable = [
        'client_id',
        'client_registration_id',
        'bank_id',
        'agreement_id',
        'bank',
        'contract_type',
        'contract_value',
        'installment_value',
        'total_installments',
        'paid_installments',
        'minimum_installments_for_refinancing',
        'contract_date',
        'first_discount_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'contract_value' => 'decimal:2',
            'installment_value' => 'decimal:2',
            'contract_date' => 'date',
            'first_discount_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientRegistration(): BelongsTo
    {
        return $this->belongsTo(ClientRegistration::class);
    }

    public function bankRecord(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function contactHistories(): HasMany
    {
        return $this->hasMany(ContactHistory::class)->latest('contacted_at');
    }

    public function refinancingNotification(): HasOne
    {
        return $this->hasOne(RefinancingNotification::class);
    }

    public function bankName(): string
    {
        return $this->bankRecord?->name ?? $this->bank ?? '-';
    }

    public function agreementName(): string
    {
        return $this->agreement?->name ?? '-';
    }

    public function contractTypeLabel(): string
    {
        return match ($this->contract_type) {
            'refinancing' => 'Refinanciamento',
            'new' => 'Novo',
            'portability' => 'Portabilidade',
            default => '-',
        };
    }

    public function refinancingStatus(): array
    {
        return app(RefinancingService::class)->calculate(
            $this->paid_installments,
            $this->minimum_installments_for_refinancing,
        );
    }
}
