<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\Datacenters;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Datacenters\Datacenters;
use LKDev\Tests\TestCase;

class DatacentersTest extends TestCase
{
    protected Datacenters $datacenters;

    public function setUp(): void
    {
        parent::setUp();
        $this->datacenters = new Datacenters($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/datacenter.json')));
        $datacenter = $this->datacenters->get(1);
        $this->assertEquals(1, $datacenter->id);
        $this->assertEquals('fsn1-dc8', $datacenter->name);
        $this->assertLastRequestEquals('GET', '/datacenters/1');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/datacenters.json')));
        $datacenter = $this->datacenters->getByName('fsn1-dc8');
        $this->assertEquals(1, $datacenter->id);
        $this->assertEquals('fsn1-dc8', $datacenter->name);
        $this->assertLastRequestQueryParametersContains('name', 'fsn1-dc8');
        $this->assertLastRequestEquals('GET', '/datacenters');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/datacenters.json')));
        $datacenters = $this->datacenters->all();

        $this->assertCount(1, $datacenters);
        $this->assertEquals(1, $datacenters[0]->id);
        $this->assertEquals('fsn1-dc8', $datacenters[0]->name);
        $this->assertLastRequestEquals('GET', '/datacenters');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/datacenters.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $datacenters = $this->datacenters->list()->datacenters;

        $this->assertCount(1, $datacenters);
        $this->assertEquals(1, $datacenters[0]->id);
        $this->assertEquals('fsn1-dc8', $datacenters[0]->name);
        $this->assertLastRequestEquals('GET', '/datacenters');
    }
}
