<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show($orderConfirmation)
    {
        $order = Order::findByConfirmationNumber($orderConfirmation);
  
        return view('orders.show', compact('order'));
    }
}
