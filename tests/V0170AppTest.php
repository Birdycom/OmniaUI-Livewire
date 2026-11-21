<?php

// File: tests/V0170AppTest.php
// Map: 0.17.0 app-migration fixes: wire.otp calls the complete method on the next tick (after wire:model has the code)

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

class V0170AppTest extends TestCase
{
    public function test_otp_complete_runs_after_the_model_update(): void
    {
        $html = $this->render('<x-oc::wire.otp model="code" complete="verify" :length="6" />');

        $this->assertStringContainsString('wire:model="code"', $html);
        $this->assertStringContainsString('x-on:input="if ($event.target.value.length >= 6) $nextTick(() => $wire.verify())"', $html);
    }
}
