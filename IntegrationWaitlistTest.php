<?php

namespace Tests\Feature;

use App\Mail\SubscriberJoined;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class IntegrationWaitlistTest extends TestCase
{

    private $validEmails = [
        'simple@example.com',
        'very.common@example.com',
        'disposable.style.email.with+symbol@example.com',
        'other.email-with-hyphen@example.com',
        'fully-qualified-domain@example.com',
        'user.name+tag+sorting@example.com',
        'x@example.com',
        'example-indeed@strange-example.com',
        'admin@mailserver1',
        'example@s.example',
    ];

    private $invalidEmails = [
        'Abc.example.com',
        'A@b@c@example.com',
        'a"b(c)d,e:f;g<h>i[j\k]l@example.com',
        'just"not"right@example.com',
        'this is"not\allowed@example.com',
        'this\ still\"not\\allowed@example.com',
    ];

    use WithFaker, DatabaseTransactions;

    function test_user_can_subscribe_to_a_waitlist()
    {
        Mail::fake();

        $email = $this->faker->safeEmail();

        $response = $this->post(route('subscribe'), compact('email'));

        Mail::assertQueued(SubscriberJoined::class, function ($mail){
            return $mail->hasTo(config('mail.from.address'));
        });

        $this->assertDatabaseHas('subscribers', compact('email'));

        $response->assertRedirect('subscribed');
    }

    function test_email_required_filter()
    {

        $response = $this->json('POST',route('subscribe'));

        $response->assertStatus(422)
            ->assertSee('The email field is required.');

    }

    function test_valid_emails()
    {
        $this->assertValidEmail();
    }

    function test_invalid_emails()
    {
        $this->assertValidEmail(false);
    }

    function test_email_unique_filter()
    {
        $email = $this->faker->safeEmail();

        Subscriber::create(compact('email'));

        $response = $this->json('POST', route('subscribe'), compact('email'));

        $response->assertStatus(422)
            ->assertSee('The email has already been taken.');
    }

    private function assertValidEmail($valid = true)
    {
        $emails = $valid ? $this->validEmails : $this->invalidEmails;

        foreach ($emails as $email) {

            $response = $this->json('POST', route('subscribe'), [
                'email' => $email
            ]);

            if($valid) {
                $response->assertStatus(302)
                    ->assertRedirect(\route('subscribed'));
            } else {
                $response->assertStatus(422)
                    ->assertSee('The email must be a valid email address.');
            }
        }
    }
}
