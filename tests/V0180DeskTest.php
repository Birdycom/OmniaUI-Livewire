<?php

// File: tests/V0180DeskTest.php
// Map: 0.18.0 desk wishes in Livewire: wire.otp also runs complete for a value from the server (and on load), once per code

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

class V0180DeskTest extends TestCase
{
    public function test_otp_complete_also_for_a_value_from_the_server(): void
    {
        $html = $this->render('<x-oc::wire.otp model="code" complete="verify" :length="6" />');

        $this->assertStringContainsString('x-init="done($el.value); $wire.$watch(&#039;code&#039;, (v) =&gt; done(v))"', $html);
        $this->assertStringContainsString('if (this.fired === v) return', $html);

        // without complete there is no Alpine at all
        $plain = $this->render('<x-oc::wire.otp model="code" />');
        $this->assertStringNotContainsString('x-data', $plain);
    }

    public function test_status_badge_and_breadcrumb_in_a_livewire_view(): void
    {
        $html = $this->render('<x-oc::badge :status="$status" />', ['status' => 'overdue']);
        $this->assertStringContainsString('data-status="overdue"', $html);
        $this->assertStringContainsString('Overdue', $html);

        $trail = $this->render('<x-oc::breadcrumb :items="$items" :max-items="3" />', ['items' => [
            ['label' => 'Dashboard', 'href' => '/'], ['label' => 'Relations', 'href' => '/r'], ['label' => 'Customers', 'href' => '/c'], ['label' => 'Company 1'],
        ]]);
        $this->assertStringContainsString('Show 1 more levels', $trail);
    }
}
