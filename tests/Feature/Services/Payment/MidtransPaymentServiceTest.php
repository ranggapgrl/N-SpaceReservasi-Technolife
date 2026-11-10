<?php

namespace Tests\Feature\Services\Payment;

use App\Services\Payment\MidtransPaymentService;
use Midtrans\Config;
use Tests\TestCase;

class MidtransPaymentServiceTest extends TestCase
{
    public function test_midtrans_requests_have_bounded_connection_and_response_timeouts(): void
    {
        config([
            'services.midtrans.is_production' => false,
            'services.midtrans.sandbox_ip' => '8.215.152.185',
        ]);

        new MidtransPaymentService;

        $this->assertSame(5, Config::$curlOptions[CURLOPT_CONNECTTIMEOUT]);
        $this->assertSame(20, Config::$curlOptions[CURLOPT_TIMEOUT]);
        $this->assertSame([
            'app.sandbox.midtrans.com:443:8.215.152.185',
            'api.sandbox.midtrans.com:443:8.215.152.185',
        ], Config::$curlOptions[CURLOPT_RESOLVE]);

        config(['services.midtrans.is_production' => true]);
        new MidtransPaymentService;

        $this->assertArrayNotHasKey(CURLOPT_RESOLVE, Config::$curlOptions);
    }
}
