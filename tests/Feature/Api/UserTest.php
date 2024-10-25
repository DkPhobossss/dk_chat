<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cant_search_users()
    {
        $response = $this->get(route('users.search', ['name' => 'abc']));

        $response->assertUnauthorized();
    }

    public function test_search_users_name_should_be_isset()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->makeOne();
        try {
            $response = $this->actingAs($user)->get(route('users.search'));
        } 
        catch (ValidationException $e) {
            $this->assertEquals('The name field is required.', $e->validator->errors()->first('name'));
            return;
        }

        $this->fail('Expected ValidationException was not thrown');
    }

    public function test_search_users_returns_user_list_with_right_sort_order()
    {
        $user = User::factory()->makeOne();

        User::factory()->count(3)->sequence(
            ['name' => 'Bob666'],
            ['name' => 'Bob'],
            ['name' => 'Batman']
        )->create();

        $response = $this->actingAs($user)->get(route('users.search', ['name' => 'Bob']));

        $response->assertOk();

        //sqlite doesnt have LOCATE :(
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            [
                'id' => 2,
                'name' => 'Bob',
            ],
        ]);
        $response->assertJsonFragment([
            [
                'id' => 1,
                'name' => 'Bob666',
            ],
        ]);
    }

    public function test_auth_user_dont_see_himself_in_search_results() {
        $users = User::factory()->count(3)->sequence(
            ['name' => 'Bob666'],
            ['name' => 'Bob'],
            ['name' => 'Batman']
        )->create();

        $response = $this->actingAs($users[1])->get(route('users.search', ['name' => 'Bob']));

        $response->assertOk();

        $response->assertExactJson([
            [
                'id' => 1,
                'name' => 'Bob666',
            ],
        ]);
    }

    public function test_sql_injection_doesnt_work_in_search(){
        $users = User::factory()->count(3)->sequence(
            ['name' => 'Bob666'],
            ['name' => 'Bob'],
            ['name' => 'Batman']
        )->create();

        //... WHERE name LIKE '%text%' ... ORDER BY LOCATE(text, name) ASC
        $response = $this->actingAs($users[0])->get(route('users.search', ['name' => "' OR 1=1 --"]));
        $response->assertOk();
        $response->assertExactJson([]);

        $response = $this->actingAs($users[0])->get(route('users.search', ['name' => "z%' OR name LIKE '%b"]));
        $response->assertOk();
        $response->assertExactJson([]);

        $response = $this->actingAs($users[0])->get(route('users.search', ['name' => "1,1);SELECT * FROM `users`;--"]));
        $response->assertOk();
        $response->assertExactJson([]);
    }
}
