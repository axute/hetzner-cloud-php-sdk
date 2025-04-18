<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\Pricing;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Prices\Prices;
use LKDev\Tests\TestCase;

class PricingTest extends TestCase
{
    protected Prices $prices;

    public function setUp(): void
    {
        parent::setUp();
        $this->prices = new Prices($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/pricing.json')));
        $prices = $this->prices->all();
        $this->assertEquals('EUR', $prices->currency);
        $this->assertEquals('19.000000', $prices->vat_rate);
        $this->assertEquals('1.0000000000', $prices->image->price_per_gb_month->net);
        $this->assertIsArray($prices->server_types);
        $this->assertLastRequestEquals('GET', '/pricing');
    }
}
