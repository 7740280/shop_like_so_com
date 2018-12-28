<?php

namespace App\Service;

use App\Exceptions\InternalException;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function store(User $user, UserAddress $userAddress, $remark, $items)
    {
        $order = DB::transaction(function () use ($user, $userAddress, $remark, $items) {
            $userAddress->update([
                'last_used_at' => Carbon::now()
            ]);

            $order = new Order([
                'address'      => [
                    'address'       => $userAddress->full_address,
                    'zip'           => $userAddress->zip,
                    'contact_name'  => $userAddress->contact_name,
                    'contact_phone' => $userAddress->contact_phone,
                ],
                'remark'       => $remark,
                'total_amount' => 0
            ]);

            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;

            foreach ($items as $value) {
                $sku  = ProductSku::find($value['sku_id']);
                $item = $order->items()->make([
                    'amount' => $value['amount'],
                    'price'  => $sku->price,
                ]);

                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();

                $totalAmount = $sku->price * $value['amount'];
                if ($sku->decreaseStock($value['amount']) <= 0) {
                    throw  new InternalException('该商品库存不足');
                }
            }

            $order->update([
                'total_amount' => $totalAmount
            ]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        // 这里我们直接使用 dispatch 函数
        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}
