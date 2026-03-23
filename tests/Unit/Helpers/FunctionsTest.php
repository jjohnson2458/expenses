<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class FunctionsTest extends TestCase
{
    public function test_e_escapes_html(): void
    {
        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', e('<script>alert("xss")</script>'));
        $this->assertEquals('Tom &amp; Jerry', e('Tom & Jerry'));
        $this->assertEquals('it&#039;s', e("it's"));
    }

    public function test_format_currency(): void
    {
        $this->assertEquals('$1,234.56', format_currency(1234.56));
        $this->assertEquals('$0.00', format_currency(0));
        $this->assertEquals('$99.90', format_currency(99.9));
        $this->assertEquals('$1,000,000.00', format_currency(1000000));
    }

    public function test_format_date(): void
    {
        $this->assertEquals('Mar 22, 2026', format_date('2026-03-22'));
        $this->assertEquals('Jan 1, 2026', format_date('2026-01-01'));

        // Custom format
        $this->assertEquals('2026-03-22', format_date('2026-03-22', 'Y-m-d'));
        $this->assertEquals('03/22/2026', format_date('2026-03-22', 'm/d/Y'));
    }

    public function test_url_helper(): void
    {
        // url() uses the APP_URL constant defined in config
        $base = APP_URL;

        $this->assertEquals(rtrim($base, '/') . '/', url(''));
        $this->assertEquals(rtrim($base, '/') . '/dashboard', url('dashboard'));
        $this->assertEquals(rtrim($base, '/') . '/expenses/create', url('expenses/create'));

        // Should handle leading slashes gracefully
        $this->assertEquals(rtrim($base, '/') . '/login', url('/login'));
    }

    public function test_csrf_field_contains_token(): void
    {
        // Ensure a CSRF token exists in session
        $_SESSION['csrf_token'] = 'test_token_abc123';

        $field = csrf_field();

        $this->assertStringContainsString('<input type="hidden"', $field);
        $this->assertStringContainsString('name="_token"', $field);
        $this->assertStringContainsString('value="test_token_abc123"', $field);
    }

    public function test_method_field(): void
    {
        $putField = method_field('put');
        $this->assertStringContainsString('<input type="hidden"', $putField);
        $this->assertStringContainsString('name="_method"', $putField);
        $this->assertStringContainsString('value="PUT"', $putField);

        $deleteField = method_field('delete');
        $this->assertStringContainsString('value="DELETE"', $deleteField);
    }
}
