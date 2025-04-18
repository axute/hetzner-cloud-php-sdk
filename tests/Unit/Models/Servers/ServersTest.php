<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\Servers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Servers\Servers;
use LKDev\Tests\TestCase;

class ServersTest extends TestCase
{
    protected Servers $servers;

    public function setUp(): void
    {
        parent::setUp();
        $this->servers = new Servers($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/server.json')));
        $server = $this->servers->get(42);
        $this->assertEquals(42, $server->id);
        $this->assertEquals('my-server', $server->name);
        $this->assertEquals('running', $server->status);
        $this->assertLastRequestEquals('GET', '/servers/42');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/servers.json')));
        $server = $this->servers->getByName('my-server');
        $this->assertEquals(42, $server->id);
        $this->assertEquals('my-server', $server->name);
        $this->assertEquals('running', $server->status);
        $this->assertLastRequestEquals('GET', '/servers');
        $this->assertLastRequestQueryParametersContains('name', 'my-server');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/servers.json')));
        $servers = $this->servers->all();

        $this->assertCount(1, $servers);
        $this->assertEquals(42, $servers[0]->id);
        $this->assertEquals('my-server', $servers[0]->name);
        $this->assertLastRequestEquals('GET', '/servers');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/servers.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $servers = $this->servers->list()->servers;

        $this->assertCount(1, $servers);
        $this->assertEquals(42, $servers[0]->id);
        $this->assertEquals('my-server', $servers[0]->name);
        $this->assertLastRequestEquals('GET', '/servers');
    }
}
