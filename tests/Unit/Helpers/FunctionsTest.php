<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class FunctionsTest extends TestCase
{
    public function test_e_escapes_html(): void
    {
        $this->assertEquals('&amp;', e('&'));
        $this->assertEquals('&lt;script&gt;', e('<script>'));
        $this->assertEquals('&quot;hello&quot;', e('"hello"'));
    }

    public function test_number_format(): void
    {
        $this->assertEquals('1,234.56', number_format(1234.56, 2));
        $this->assertEquals('0.00', number_format(0, 2));
    }

    public function test_url_helper(): void
    {
        $url = url('/dashboard');
        $this->assertStringContainsString('/dashboard', $url);
    }

    public function test_now_returns_carbon(): void
    {
        $this->assertInstanceOf(\Carbon\Carbon::class, now());
    }
}
