<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function __construct(private PaymentGatewayManager $manager) {}

    public function push(Request $request, string $gateway): JsonResponse
    {
        return $this->manager->gateway($gateway)->push($request);
    }

    public function queryStatus(Request $request, string $gateway): JsonResponse
    {
        return $this->manager->gateway($gateway)->queryStatus($request);
    }

    public function callback(Request $request, string $gateway): JsonResponse
    {
        return $this->manager->gateway($gateway)->callback($request);
    }
}