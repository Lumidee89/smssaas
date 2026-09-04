<?php

namespace App\Services\Campus;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function transact(Wallet $wallet, string $type, float $amount, string $reference, string $description, ?int $userId = null, array $metadata = []): WalletTransaction
    {
        if ($amount <= 0 || ! in_array($type, ['credit', 'debit'], true)) throw ValidationException::withMessages(['amount' => 'Enter a positive amount and a valid transaction type.']);
        return DB::transaction(function () use ($wallet, $type, $amount, $reference, $description, $userId, $metadata) {
            if ($existing = WalletTransaction::where('reference', $reference)->first()) {
                if ($existing->wallet_id !== $wallet->id) throw ValidationException::withMessages(['reference' => 'This transaction reference is already in use.']);
                return $existing;
            }
            $locked = Wallet::lockForUpdate()->findOrFail($wallet->id);
            if (! $locked->is_active) throw ValidationException::withMessages(['wallet' => 'This wallet is inactive.']);
            $before = (float) $locked->balance;
            $after = $type === 'credit' ? $before + $amount : $before - $amount;
            if ($after < 0) throw ValidationException::withMessages(['amount' => 'Insufficient wallet balance.']);
            $locked->update(['balance' => $after]);
            return WalletTransaction::create(['public_id' => (string) Str::uuid(), 'school_id' => $locked->school_id, 'wallet_id' => $locked->id, 'type' => $type, 'amount' => $amount, 'balance_before' => $before, 'balance_after' => $after, 'reference' => $reference, 'description' => $description, 'initiated_by' => $userId, 'metadata' => $metadata]);
        });
    }
}
