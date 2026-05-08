<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_the_application_handles_unknown_health_endpoint(): void
    {
        $response = $this->get('/up');

        $response->assertNotFound();
    }
}
