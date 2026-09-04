<?php

namespace App\Services\Campus;

use App\Models\MarketplaceOrder;
use App\Models\MarketplaceOrderItem;
use App\Models\MarketplaceProduct;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplaceService
{
    public function __construct(private WalletService $wallets) {}

    public function order(int $schoolId, int $studentId, array $lines, int $userId): MarketplaceOrder
    {
        return DB::transaction(function () use ($schoolId, $studentId, $lines, $userId) {
            $products = []; $total = 0;
            foreach ($lines as $line) {
                $product = MarketplaceProduct::where('school_id', $schoolId)->where('is_active', true)->lockForUpdate()->findOrFail($line['product_id']);
                $quantity = (int) $line['quantity'];
                if ($quantity < 1 || $product->stock_quantity < $quantity) throw ValidationException::withMessages(['items' => "Insufficient stock for {$product->name}."]);
                $products[] = [$product, $quantity]; $total += (float) $product->price * $quantity;
            }
            $wallet = Wallet::firstOrCreate(['school_id' => $schoolId, 'student_id' => $studentId], ['currency' => 'NGN']);
            $number = 'ORD-'.now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $transaction = $this->wallets->transact($wallet, 'debit', $total, 'marketplace:'.$number, 'Marketplace order '.$number, $userId);
            $order = MarketplaceOrder::create(['order_number' => $number, 'school_id' => $schoolId, 'student_id' => $studentId, 'wallet_transaction_id' => $transaction->id, 'total' => $total, 'status' => 'paid', 'created_by' => $userId]);
            foreach ($products as [$product, $quantity]) {
                $product->decrement('stock_quantity', $quantity);
                MarketplaceOrderItem::create(['marketplace_order_id' => $order->id, 'marketplace_product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => $product->price, 'total' => (float) $product->price * $quantity]);
            }
            return $order->load('items');
        });
    }
}
