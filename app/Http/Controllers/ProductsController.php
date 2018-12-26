<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $builder = Product::query()->where('on_sale', true);

        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';

            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(desc|asc)/', $order, $m)) {
                if (in_array($m[1], ['price', 'rating', 'sold_count'])) {
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $builder->paginate(16);
        return view('products.index', [
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
        ]);
    }


    public function show(Product $product, Request $request)
    {
        if (!$product->on_sale) {
            throw  new InvalidRequestException('该商品未上架');
        }

        $favorite = false;

        if ($user = $request->user()) {
            $favorite = boolval($user->favoriteProducts()->find($product->id));
        }

        return view('products.show', ['product' => $product, 'favored' => $favorite]);
    }


    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }


    public function disFavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);
        return [];
    }
}
