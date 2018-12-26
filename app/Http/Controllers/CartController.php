<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(AddCartRequest $request)
    {
        $user   = $request->user();
        $sku_id = $request->input('sku_id');
        $amount = $request->input('amount');

        if ($cart = $user->cartItem()->where('product_sku_id', $sku_id)->first()) {
            //如果存在sku则增加数量
            $cart->update([
                'amount' => $cart->amount + $amount
            ]);

        } else {
            //否则添加一个新的购物车记录
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($sku_id);
            $cart->save();
        }

        return;
    }

    public function index(Request $request)
    {
        $cartItems = $request->user()->cartItem()->with(['productSku.product'])->get();
        return view('cart.index', ['cartItems' => $cartItems]);
    }


    public function remove(ProductSku $productSku, Request $request)
    {
        $request->user()->cartItem()->where('product_sku_id', $productSku->id)->delete();
        return;
    }
}
