<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class UserFilterTest extends TestCase
{
    /**
     * Test to return only male users
     */

    public function test_only_male_users_are_returned()
    {
        Http::fake([
            'https://randomuser.me/api/*' => Http::response([
                'results' => [
                    ['gender' => 'male',   'name' => ['first' => 'John', 'last' => 'Doe'], 'email' => 'john@example.com', 'nat' => 'US'],
                    ['gender' => 'female', 'name' => ['first' => 'Jenny', 'last' => 'Doe'], 'email' => 'jenny@example.com', 'nat' => 'US'],
                ]
            ])
        ]);

        $response = $this->get('/users?gender=male');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jenny Doe');
    }
}
