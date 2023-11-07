<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Actions\Bookshop\InsertDataBookMenuAction;
use Auth;

class CartController extends Controller
{



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()) {

            return redirect()->route('login');
        }
        $carts = Cart::all()->where('user_id', Auth::user()->id)->where('product_order', 'no');
        $carts_amount = DB::table('carts')->where('user_id', Auth::user()->id)->where('product_order', 'no')->count();
        $discount_price = 0;
        $without_discount_price = DB::table('carts')->where('user_id', Auth::user()->id)->where('product_order', 'no')->sum('subtotal');

        $coupon_code = NULL;

        if ($carts_amount > 0) {
            foreach ($carts as $cart) {

                $coupon_code = $cart->coupon_id;
            }
        }

        if ($coupon_code != NULL) {


            $validate = DB::table('coupons')->where('code', $coupon_code)->value('validate');

            $today = date("Y-m-d");

            if ($validate < $today) {


                $total_price = DB::table('carts')->where('user_id', Auth::user()->id)->where('product_order', 'no')->sum('subtotal');
            } else {

                $total_price = DB::table('carts')->where('user_id', Auth::user()->id)->where('product_order', 'no')->sum('subtotal');


                $coupon_code_price = DB::table('coupons')->where('code', $coupon_code)->value('percentage');

                $discount_price = (($total_price * $coupon_code_price) / 100);
                $discount_price = floor($discount_price);


                $total_price = $total_price - $discount_price;
            }
        } else {

            $total_price = DB::table('carts')->where('user_id', Auth::user()->id)->where('product_order', 'no')->sum('subtotal');
        }
        $extra_charge = DB::table('charges')->get();
        $total_extra_charge = DB::table('charges')->sum('price');


        return view("cart", compact('carts', 'total_price', 'discount_price', 'without_discount_price', 'extra_charge', 'total_extra_charge'));
    }

    public function getAllUpdate()
    {
        $carts = DB::table('carts')->where('id',8)->get();
        // dd($carts);
        foreach ($carts as $key => $cart) {

            $values = [

                $cart->id,
                $cart->product_id,
                $cart->user_id,
                $cart->product_order,
                $cart->shipping_address,
                $cart->name,
                $cart->price,
                $cart->quantity,
                $cart->subtotal,
                $cart->coupon_id,
                $cart->pay_method,
                $cart->invoice_no,
                $cart->delivery_time,
                $cart->purchase_date

            ];
            InsertDataBookMenuAction::Update('carts_menu', $values, $cart->id, 'carts');
        }

        session()->flash('success', 'Carts sheet updated successfully !');

        return back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {

        if (!Auth::user()) {

            return redirect()->route('login');
        }

        $product = Product::find($id);
        $quantity = $request->number;
        if (Cart::where('product_id', '=', $id)->where('user_id', Auth::user()->id)->where('product_order', 'no')->exists()) {
            $quant = DB::table('carts')->where('product_id', '=', $id)->where('user_id', Auth::user()->id)->where('product_order', 'no')->value('quantity');


            $quantity = $quantity + (int) $quant;

            DB::table('carts')->where('product_id', '=', $id)->where('user_id', Auth::user()->id)->where('product_order', 'no')->update([
                'quantity' => $quantity,
                'subtotal' => $quantity * $product->price
            ]);
            $id = DB::table('carts')->where('product_id', '=', $id)->where('user_id', Auth::user()->id)->where('product_order', 'no')->first();
            $values = [
                [$id->id, $id->product_id, Auth::user()->id, "no", 'N/A', $id->name, $id->price, $quantity, $quantity * $product->price],
            ];


            if (InsertDataBookMenuAction::Update('carts_menu', $values, $id->id, 'carts') != null) {
                return back();
            }
        } else {
            $insert = DB::table('carts')->insertGetId([
                'product_id' => $product->id,
                'user_id' => Auth::user()->id,
                'product_order' => "no",
                'shipping_address' => 'N/A',
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $quantity * $product->price
            ]);
            $values = [
                [$insert, $product->id, Auth::user()->id, "no", 'N/A', $product->name, $product->price, $quantity, $quantity * $product->price],
            ];


            if (InsertDataBookMenuAction::Insert('carts_menu', $values, $insert, 'carts') != null) {
                return back();
            }
        }


        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Cart::find($id);
        $product->delete();

        return redirect()->route('cart');
    }




    public function checkout($total)
    {
        return view("checkout", compact('total'));
    }
}
