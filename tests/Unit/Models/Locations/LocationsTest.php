<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\Locations;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Locations\Locations;
use LKDev\Tests\TestCase;

class LocationsTest extends TestCase
{
    protected Locations $locations;

    public function setUp(): void
    {
        parent::setUp();
        $this->locations = new Locations($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/location.json')));
        $location = $this->locations->get(1);
        $this->assertEquals(1, $location->id);
        $this->assertEquals('fsn1', $location->name);

        $this->assertLastRequestEquals('GET', '/locations/1');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/locations.json')));
        $location = $this->locations->getByName('fsn1');
        $this->assertEquals(1, $location->id);
        $this->assertEquals('fsn1', $location->name);
        $this->assertLastRequestEquals('GET', '/locations');
        $this->assertLastRequestQueryParametersContains('name', 'fsn1');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/locations.json')));
        $locations = $this->locations->all();

        $this->assertCount(1, $locations);
        $this->assertEquals(1, $locations[0]->id);
        $this->assertEquals('fsn1', $locations[0]->name);
        $this->assertLastRequestEquals('GET', '/locations');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/locations.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $locations = $this->locations->list()->locations;

        $this->assertCount(1, $locations);
        $this->assertEquals(1, $locations[0]->id);
        $this->assertEquals('fsn1', $locations[0]->name);
        $this->assertLastRequestEquals('GET', '/locations');
    }
}
