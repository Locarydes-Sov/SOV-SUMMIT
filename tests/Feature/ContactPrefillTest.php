<?php

namespace Tests\Feature;

use Tests\TestCase;

class ContactPrefillTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_davos_link_prefills_programme_destination_and_service(): void
    {
        $this->get(route('contact', [
            'programme' => 'Davos 2027',
            'destination' => 'Davos, Switzerland',
            'service' => 'Delegation Management',
        ]))
            ->assertOk()
            ->assertSee('name="programme_type" value="Davos 2027"', false)
            ->assertSee('name="destination" value="Davos, Switzerland"', false)
            ->assertSee('<option selected>Delegation Management</option>', false);
    }

    public function test_contact_form_is_empty_without_query_values(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('name="programme_type" value=""', false)
            ->assertDontSee('<option selected>', false);
    }
}
