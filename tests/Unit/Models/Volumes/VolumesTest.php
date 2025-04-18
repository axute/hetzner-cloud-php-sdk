<?php

namespace LKDev\Tests\Unit\Models\Volumes;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Locations\LocationReference;
use LKDev\HetznerCloud\Models\Volumes\Volumes;
use LKDev\Tests\TestCase;

class VolumesTest extends TestCase
{
    protected Volumes $volumes;

    public function setUp(): void
    {
        parent::setUp();
        $this->volumes = new Volumes($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     * @noinspection PhpUndefinedFieldInspection
     */
    public function testCreate()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/volume_create.json')));
        $resp = $this->volumes->create('database-storage', 42, null, new LocationReference(id: 1, name: 'nbg1'));

        $volume = $resp->getResponsePart('volume');
        $this->assertEquals(4711, $volume->id);
        $this->assertEquals('database-storage', $volume->name);
        $this->assertEquals(12, $volume->server);
        $this->assertEquals(1, $volume->location->id);

        $this->assertNotNull($resp->actions);
        $this->assertIsArray($resp->next_actions);

        $this->assertLastRequestEquals('POST', '/volumes');
        $this->assertLastRequestBodyParametersEqual(['name' => 'database-storage', 'size' => 42, 'location' => 'nbg1']);
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/volumes.json')));
        $volume = $this->volumes->getByName('database-storage');
        $this->assertEquals(4711, $volume->id);
        $this->assertEquals('database-storage', $volume->name);
        $this->assertEquals(12, $volume->server);
        $this->assertEquals(1, $volume->location->id);

        $this->assertLastRequestEquals('GET', '/volumes');
        $this->assertLastRequestQueryParametersContains('name', 'database-storage');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/volume.json')));
        $volume = $this->volumes->get(4711);
        $this->assertEquals(4711, $volume->id);
        $this->assertEquals('database-storage', $volume->name);
        $this->assertEquals(12, $volume->server);
        $this->assertEquals(1, $volume->location->id);

        $this->assertLastRequestEquals('GET', '/volumes/4711');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/volumes.json')));
        $volumes = $this->volumes->all();
        $this->assertCount(1, $volumes);
        $volume = $volumes[0];
        $this->assertEquals(4711, $volume->id);
        $this->assertEquals('database-storage', $volume->name);
        $this->assertEquals(12, $volume->server);
        $this->assertEquals(1, $volume->location->id);

        $this->assertLastRequestEquals('GET', '/volumes');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/volumes.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $volumes = $this->volumes->list()->volumes;
        $this->assertCount(1, $volumes);
        $volume = $volumes[0];
        $this->assertEquals(4711, $volume->id);
        $this->assertEquals('database-storage', $volume->name);
        $this->assertEquals(12, $volume->server);
        $this->assertEquals(1, $volume->location->id);

        $this->assertLastRequestEquals('GET', '/volumes');
    }
}
