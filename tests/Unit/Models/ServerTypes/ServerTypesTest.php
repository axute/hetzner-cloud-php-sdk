<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\ServerTypes;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Servers\Types\ServerTypes;
use LKDev\Tests\TestCase;

class ServerTypesTest extends TestCase
{
    protected ServerTypes $server_types;

    public function setUp(): void
    {
        parent::setUp();
        $this->server_types = new ServerTypes($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/server_type.json')));
        $server_type = $this->server_types->get(1);
        $this->assertEquals(1, $server_type->id);
        $this->assertEquals('cx11', $server_type->name);
        $this->assertLastRequestEquals('GET', '/server_types/1');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/server_types.json')));
        $server_type = $this->server_types->getByName('cx11');
        $this->assertEquals(1, $server_type->id);
        $this->assertEquals('cx11', $server_type->name);
        $this->assertLastRequestEquals('GET', '/server_types');
        $this->assertLastRequestQueryParametersContains('name', 'cx11');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/server_types.json')));
        $server_types = $this->server_types->all();

        $this->assertCount(1, $server_types);
        $this->assertEquals(1, $server_types[0]->id);
        $this->assertEquals('cx11', $server_types[0]->name);
        $this->assertLastRequestEquals('GET', '/server_types');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/server_types.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $server_types = $this->server_types->list()->server_types;

        $this->assertCount(1, $server_types);
        $this->assertEquals(1, $server_types[0]->id);
        $this->assertEquals('cx11', $server_types[0]->name);
        $this->assertLastRequestEquals('GET', '/server_types');
    }
}
