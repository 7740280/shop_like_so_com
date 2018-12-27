<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\Request;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Carbon\Carbon;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(OrderRequest $request)
    {
        $user  = $request->user();
        $order = DB::transaction(function () use ($user, $request) {
            $address = UserAddress::find($request->input('address_id'));
            $address->update([
                'last_used_at' => Carbon::now()
            ]);

            $order = new Order([
                'address'      => [
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => $request->input('remark'),
                'total_amount' => 0,
            ]);
            //关联当前用户
            $order->user()->associate($user);
            //写入数据库
            $order->save();

            $totalAmount = 0;
            $items       = $request->input('items');

            foreach ($items as $value) {
                $sku = ProductSku::find($value['sku_id']);

                $item = $order->items()->make([
                    'amount' => $value['amount'],
                    'price'  => $sku->price,
                ]);

                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $value['amount'];
                if ($sku->decreaseStock($value['amount']) <= 0) {
                    throw new InvalidArgumentException('该商品库存不足');
                }
            }
            //更新订单总金额
            $order->update(['total_amount' => $totalAmount]);
            //将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id');
            $user->cartItem()->whereIn('product_sku_id', $skuIds)->delete();

            return $order;
        });

        $this->dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }

    public function index(Request $request)
    {
        $orders = Order::query()->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }
}
