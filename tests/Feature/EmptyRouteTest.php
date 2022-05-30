<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmptyRouteTest extends TestCase
{
    use RefreshDatabase;

    public function testEmptyRouteSucceeds()
    {
        $response = $this->get("/?shop=myshop");
        $response->assertStatus(302);
    }
}
