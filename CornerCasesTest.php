<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class CornerCasesTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    function test_very_small_email()
    {
        $response = $this->json('POST', route('subscribe'), [
            'email' => 'a@b.c'
        ]);

        $response->assertStatus(302)
            ->assertRedirect('subscribed');
    }

    function test_large_email($stringLength = 30)
    {
        $largeEmail = $this->generateRandomString($stringLength) . "@" . $this->generateRandomString($stringLength) . "." . $this->generateRandomString(30);

        $response = $this->json('POST',route('subscribe'), [
            'email' => $largeEmail
        ]);

        $response->assertStatus(302)
            ->assertRedirect('subscribed');
    }

    function test_very_large_email()
    {
        $this->test_large_email(100);
    }

    function test_lots_of_request_from_same_user_agent()
    {
        $userAgent = $this->faker->unique()->userAgent();

        for($x = 0; $x <= 10; $x++) {
            $response = $this->withHeader('HTTP_USER-AGENT', $userAgent)
                ->json('POST', route('subscribe'), [
                    'email' => $this->faker->safeEmail()
                ]);

            $response->assertStatus(302)
                ->assertRedirect('subscribed');
        }

    }

    private function generateRandomString($length = 30) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
