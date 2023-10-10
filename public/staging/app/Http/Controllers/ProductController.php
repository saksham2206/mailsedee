<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Acelle\Model\MailList;
use Acelle\Model\EmailVerificationServer;
use Acelle\Events\MailListSubscription;
use Acelle\Model\Setting;
use Acelle\Model\Customer;
use Acelle\Model\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('products.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        $products = Product::search($request)->paginate($request->per_page);
        $view = $request->view ? $request->view : 'grid';

        return view('products._list_' . $view, [
            'products' => $products,
        ]);
    }

    public function image(Request $request, $uid)
    {
        $product = Product::findByUid($uid);

        if ($product->getImagePath()) {
            return response()->file($product->getImagePath());
        } else {
            return response()->file(public_path('image/no-product-image.png'));
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function json(Request $request)
    {
        // listing product for
        if ($request->action == 'list') {
            return response()->json(
                Product::search($request)->paginate($request->per_page)
                    ->map(function ($product, $key) {
                        return [
                            'id' => $product->uid,
                            'name' => $product->title,
                            'price' => format_price($product->price),
                            'image' => action('ProductController@image', $product->uid),
                            'description' => substr(strip_tags($product->description), 0, 100),
                            'link' => action('ProductController@index'),
                        ];
                    })->toArray()
            );
        }

        // return a product info
        if ($request->product_id) {
            $product = Product::findByUid($request->product_id);

            return response()->json([
                'id' => $product->uid,
                'name' => $product->title,
                'price' => format_price($product->price),
                'image' => action('ProductController@image', $product->uid),
                'description' => substr(strip_tags($product->description), 0, 100),
                'link' => action('ProductController@index'),
            ]);
        }

        $results = Product::search($request)->paginate($request->per_page)
        ->map(function ($item, $key) {
            return ['text' => $item->title, 'id' => $item->uid];
        })->toArray();

        $json = '{
            "items": ' .json_encode($results). ',
            "more": ' . (empty($results) ? 'false' : 'true') . '
        }';

        return $json;
    }
}
