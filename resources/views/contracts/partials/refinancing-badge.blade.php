@if ($refinancing['status'] === 'eligible')
    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Elegivel</span>
@else
    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ $refinancing['remaining_installments'] }} restantes</span>
@endif
