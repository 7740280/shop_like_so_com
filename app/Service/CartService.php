<?php

namespace App\Service;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function get()
    {
        return Auth::user()->cartItem()->with(['productSku.product'])->get();
    }

    public function add($sku_id, $amount)
    {
        $user = Auth::user();
        if ($cart = $user->cartItem()->where('product_sku_id', $sku_id)->first()) {
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        } else {
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($sku_id);
            $cart->save();
        }

        return $cart;
    }

    public function remove($skuIds)
    {
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItem()->whereIn('product_sku_id', $skuIds)->delete();
        return;
    }
}
