@extends('layouts.app')
@section('title', 'Promo codes')
@section('content')
<div class="page-head"><div><span class="eyebrow">BONUS CENTER</span><h1>Redeem a promo code</h1><p class="hint">Promo codes grant virtual credits only and have no cash value.</p></div><div class="balance"><span>Balance</span><strong data-live-balance>{{ number_format(auth()->user()->wallet->balance) }}</strong></div></div>
<section class="panel promo-redeem">
<form method="post" action="{{ route('promos.redeem') }}" class="promo-form">@csrf
<label>Promo code<input name="code" value="{{ old('code') }}" maxlength="40" placeholder="WELCOME500" required></label>
<button class="button" type="submit">Redeem code</button>
</form>
</section>
<section class="panel"><h2>Redemption history</h2><div class="table-wrap"><table><thead><tr><th>Code</th><th>Title</th><th>Credits</th><th>Redeemed</th></tr></thead><tbody>
@forelse($redemptions as $redemption)<tr><td><code>{{ $redemption->promoCode->code }}</code></td><td>{{ $redemption->promoCode->title }}</td><td class="positive">+{{ number_format($redemption->credits) }}</td><td>{{ $redemption->redeemed_at->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="4">No promo codes redeemed yet.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
