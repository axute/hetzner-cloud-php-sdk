<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\FloatingIPs;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\FloatingIps\FloatingIps;
use LKDev\HetznerCloud\Models\Locations\LocationReference;
use LKDev\HetznerCloud\Models\Servers\ServerReference;
use LKDev\Tests\TestCase;

class FloatingIPsTest extends TestCase
{
    protected FloatingIps $floatingIps;

    public function setUp(): void
    {
        parent::setUp();
        $this->floatingIps = new FloatingIps($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/floatingIP.json')));
        $floatingIp = $this->floatingIps->get(1);
        $this->assertEquals(4711, $floatingIp->id);
        $this->assertEquals('Web Frontend', $floatingIp->description);
        $this->assertLastRequestEquals('GET', '/floating_ips/1');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/floatingIPs.json')));
        $floatingIp = $this->floatingIps->getByName('Web Frontend');
        $this->assertEquals(4711, $floatingIp->id);
        $this->assertEquals('Web Frontend', $floatingIp->name);

        $this->assertLastRequestQueryParametersContains('name', 'Web Frontend');
        $this->assertLastRequestEquals('GET', '/floating_ips');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/floatingIPs.json')));
        $floatingIps = $this->floatingIps->all();

        $this->assertCount(1, $floatingIps);
        $this->assertEquals(4711, $floatingIps[0]->id);
        $this->assertEquals('Web Frontend', $floatingIps[0]->description);
        $this->assertLastRequestEquals('GET', '/floating_ips');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/floatingIPs.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $floatingIps = $this->floatingIps->list()->floating_ips;

        $this->assertCount(1, $floatingIps);
        $this->assertEquals(4711, $floatingIps[0]->id);
        $this->assertEquals('Web Frontend', $floatingIps[0]->description);
        $this->assertLastRequestEquals('GET', '/floating_ips');
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testCreateWithLocation()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/floatingIP.json')));
        $floatingIp = $this->floatingIps->create('ipv4', 'Web Frontend', new LocationReference(id: 123, name: 'nbg1'), null, 'my-fip', ['key' => 'value']);

        $this->assertEquals(4711, $floatingIp->id);
        $this->assertEquals('Web Frontend', $floatingIp->description);
        $this->assertLastRequestEquals('POST', '/floating_ips');
        $this->assertLastRequestBodyParametersEqual(['type'          => 'ipv4',
                                                     'description'   => 'Web Frontend',
                                                     'home_location' => 'nbg1',
                                                     'name'          => 'my-fip',
                                                     'labels'        => ['key' => 'value']
        ]);
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testCreateWithServer()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/floatingIP.json')));
        $floatingIp = $this->floatingIps->create('ipv4', 'Web Frontend', null, new ServerReference(id: 23));

        $this->assertEquals(4711, $floatingIp->id);
        $this->assertEquals('Web Frontend', $floatingIp->description);
        $this->assertLastRequestEquals('POST', '/floating_ips');
        $this->assertLastRequestBodyParametersEqual(['type'        => 'ipv4',
                                                     'description' => 'Web Frontend',
                                                     'server'      => 23
        ]);
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testCreateWithName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/floatingIP.json')));
        $floatingIp = $this->floatingIps->create('ipv4', 'Web Frontend', new LocationReference(id: 123, name: 'nbg1'), null, 'WebServer');

        $this->assertEquals(4711, $floatingIp->id);
        $this->assertEquals('Web Frontend', $floatingIp->description);
        $this->assertLastRequestEquals('POST', '/floating_ips');
        $this->assertLastRequestBodyParametersEqual(['type'          => 'ipv4',
                                                     'description'   => 'Web Frontend',
                                                     'home_location' => 'nbg1'
        ]);
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testDelete()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/floatingIP.json')));
        $floatingIp = $this->floatingIps->get(4711);
        $this->assertLastRequestEquals('GET', '/floating_ips/4711');

        $this->mockHandler->append(new Response(204, []));
        $this->assertTrue($floatingIp->delete());
        $this->assertLastRequestEquals('DELETE', '/floating_ips/4711');
    }
}
