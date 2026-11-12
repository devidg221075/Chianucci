<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function valid(array $override = []): array
    {
        return $override + [
            'nome' => 'Mario Rossi', 'azienda' => 'Oro Moda S.r.l.', 'email' => 'mario@example.com',
            'telefono' => '0575 000000', 'messaggio' => 'Vorrei informazioni sulle catene.', 'consenso' => '1',
            'sito_web' => '',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
        Mail::fake();
    }

    public function test_contact_page_shows_the_form_in_both_languages(): void
    {
        $this->get('/it/contatti')->assertOk()->assertSee('name="messaggio"', false)->assertSee('Invia richiesta');
        $this->get('/en/contacts')->assertOk()->assertSee('name="messaggio"', false)->assertSee('Send request');
    }

    public function test_valid_submission_sends_mail_to_info(): void
    {
        $this->post('/it/contatti', $this->valid())->assertRedirect();

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
            return $mail->hasTo('info@chianucci.it')
                && $mail->hasReplyTo('mario@example.com')
                && str_contains($mail->render(), 'Vorrei informazioni sulle catene.');
        });
    }

    public function test_mail_text_keeps_apostrophes_and_ampersands_readable(): void
    {
        $text = (new ContactMessage($this->valid(['azienda' => "Oro & Moda d'Arezzo", 'messaggio' => "L'ordine <urgente> & altro"]), 'it'))->render();

        $this->assertStringContainsString("Oro & Moda d'Arezzo", $text);
        $this->assertStringContainsString("L'ordine <urgente> & altro", $text);
        $this->assertStringNotContainsString('&amp;', $text);
        $this->assertStringNotContainsString('&#039;', $text);
    }

    public function test_missing_consent_or_invalid_email_is_rejected(): void
    {
        $this->post('/it/contatti', $this->valid(['consenso' => null]))->assertSessionHasErrors('consenso');
        $this->post('/it/contatti', $this->valid(['email' => 'non-una-email']))->assertSessionHasErrors('email');
        $this->post('/it/contatti', $this->valid(['messaggio' => '']))->assertSessionHasErrors('messaggio');

        Mail::assertNothingSent();
    }

    public function test_honeypot_filled_is_silently_discarded(): void
    {
        $this->post('/it/contatti', $this->valid(['sito_web' => 'http://spam.example']))->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_header_injection_in_name_or_email_is_rejected(): void
    {
        $this->post('/it/contatti', $this->valid(['email' => "a@b.it\r\nBcc: spam@example.com"]))->assertSessionHasErrors('email');
        $this->post('/it/contatti', $this->valid(['nome' => "Mario\r\nBcc: spam@example.com"]))->assertSessionHasErrors('nome');

        Mail::assertNothingSent();
    }

    public function test_array_or_oversized_fields_are_rejected_not_crashing(): void
    {
        $this->post('/it/contatti', $this->valid(['nome' => ['x']]))->assertSessionHasErrors('nome');
        $this->post('/it/contatti', $this->valid(['messaggio' => str_repeat('a', 6000)]))->assertSessionHasErrors('messaggio');
    }

    public function test_form_is_rate_limited(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post('/it/contatti', $this->valid())->assertRedirect();
        }

        $this->post('/it/contatti', $this->valid())->assertStatus(429);
    }

    public function test_english_form_works_and_uses_english_path(): void
    {
        $this->post('/en/contacts', $this->valid())->assertRedirect();

        Mail::assertSent(ContactMessage::class);
    }
}
