<?php

namespace App\Http\Controllers;

use App\Models\BlackListCustomer;
use Illuminate\Http\Request;

class DevController extends Controller
{
    function black_list_customers() {
        $black_list_customers = BlackListCustomer::distinct()->get(['customer_id']);;
        return view('backend.customer.black_list_customer', compact('black_list_customers'));
    }
}
