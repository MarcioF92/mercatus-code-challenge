<?php

namespace Tests\Browser;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class BrowserWaitlistTest extends DuskTestCase
{
    use DatabaseTransactions;

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

    use WithFaker;

    function test_a_user_can_subscribe()
    {

        $email = $this->faker->safeEmail();

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit(route('waitlist'))
                    ->type('email', $email)
                    ->press(__('Request early access'))
                    ->assertRouteIs('subscribed');
        });
    }

    function test_user_cant_subscribe_an_empty_email()
    {
        $this->browse(function (Browser $browser){
            $browser->visit(route('waitlist'))
                ->press(__('Request early access'))
                ->assertFocused('email');
        });
    }

    function test_user_cant_subscribe_an_existing_email()
    {
        $email = $this->faker->safeEmail();

        Subscriber::create(compact('email'));

        $this->browse(function (Browser $browser) use ($email){
            $browser->visit(route('waitlist'))
                ->type('email', $email)
                ->press(__('Request early access'))
                ->assertRouteIs('waitlist')
                ->assertSee('The email has already been taken.');
        });
    }

    function test_user_cant_subscribe_invalid_emails()
    {
        foreach ($this->invalidEmails as $email) {

            $this->browse(function (Browser $browser) use ($email){
                $browser->visit(route('waitlist'))
                    ->type('email', $email)
                    ->press(__('Request early access'))
                    ->assertRouteIs('waitlist')
                    ->assertSee('The email must be a valid email address.');
            });

        }


    }

    function test_user_can_subscribe_valid_emails()
    {
        foreach ($this->validEmails as $email) {

            $this->browse(function (Browser $browser) use ($email){
                $browser->visit(route('waitlist'))
                    ->type('email', $email)
                    ->press(__('Request early access'))
                    ->assertRouteIs('subscribed');
            });
        }
    }

    function test_user_cant_double_click_subscribe_form_button()
    {
        $email = $this->faker->safeEmail();

        $this->browse(function (Browser $browser) use ($email){
            $browser->visit(route('waitlist'))
                ->type('email', $email)
                ->doubleClick(__('Request early access'))
                ->assertRouteIs('waitlist');
                //->assertSee('The email has already been taken.');
        });
    }
}
