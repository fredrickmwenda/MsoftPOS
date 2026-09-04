<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class StorefrontPageTest extends TestCase
{
    use WithoutMiddleware;

    public function test_storefront_page_loads_with_categories_and_products():
    {
        $response = $this->get('/shop');

        $response->assertOk();
        $response->assertViewHas('categories');
        $response->assertViewHas('products');
    }
}
