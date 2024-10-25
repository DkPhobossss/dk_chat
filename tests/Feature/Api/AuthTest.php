<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_xsrf_token_route_is_up() {
        $response = $this->get('/sanctum/csrf-cookie');

        $response->assertStatus(204);

        $cookies = $response->headers->getCookies();

        $this->assertNotEmpty($cookies);
        $this->assertEquals('XSRF-TOKEN', $cookies[0]->getName());
    }

    public function test_guest_cant_access_broadcast_auth_route() {
        $response = $this->post('/broadcasting/auth');

        $response->assertForbidden();
    }

    //logic for broadcasting more complicated for this task
}
