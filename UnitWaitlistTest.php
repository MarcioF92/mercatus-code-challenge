<?php

namespace Tests\Unit;

use App\Models\Subscriber;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class UnitWaitlistTest extends TestCase
{
    use WithFaker;

    function test_subscriber_creation()
    {
        $email = $this->faker->safeEmail();
        $subscriber = new Subscriber(compact('email'));
        $this->assertTrue($email == $subscriber->email);
    }

    function test_guest_user_can_see_waitlist_page()
    {

        $response = $this->get(route('waitlist'));

        $response->assertStatus(200)
            ->assertViewIs('waitlist');

    }

    function test_user_can_see_subscribed_page()
    {
        $response = $this->get(route('subscribed'));

        $response->assertStatus(200)
            ->assertViewIs('subscribed');
    }

    function test_authorized_user_cant_see_waitlist_page()
    {

        $user = \factory(User::class)->create();

        $response = $this->actingAs($user)
                        ->get(route('waitlist'));

        // This test will no pass, but well,
        // I think that a logged user may not see the waitlist page
        // because it was made for unexisting users
        $response->assertStatus(401);
    }
}
