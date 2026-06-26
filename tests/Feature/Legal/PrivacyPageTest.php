<?php

namespace Tests\Feature\Legal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_page_is_accessible(): void
    {
        $this->get('/privacy')->assertOk();
    }
}
