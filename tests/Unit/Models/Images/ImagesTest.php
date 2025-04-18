<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\Images;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Images\Images;
use LKDev\Tests\TestCase;

class ImagesTest extends TestCase
{
    protected Images $images;

    public function setUp(): void
    {
        parent::setUp();
        $this->images = new Images($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/image.json')));
        $image = $this->images->get(4711);
        $this->assertEquals(4711, $image->id);
        $this->assertEquals('ubuntu-20.04', $image->name);

        $this->assertEmpty($image->labels);

        $this->assertLastRequestEquals('GET', '/images/4711');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/images.json')));
        $image = $this->images->getByName('ubuntu-20.04');
        $this->assertEquals(4711, $image->id);
        $this->assertEquals('ubuntu-20.04', $image->name);

        $this->assertEmpty($image->labels);
        $this->assertLastRequestEquals('GET', '/images');
        $this->assertLastRequestQueryParametersContains('name', 'ubuntu-20.04');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGetByNameWithArchitecture()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/images.json')));
        $image = $this->images->getByName('ubuntu-20.04', 'arm');
        $this->assertEquals(4711, $image->id);
        $this->assertEquals('ubuntu-20.04', $image->name);

        $this->assertEmpty($image->labels);
        $this->assertLastRequestEquals('GET', '/images');
        $this->assertLastRequestQueryParametersContains('name', 'ubuntu-20.04');
        $this->assertLastRequestQueryParametersContains('architecture', 'arm');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/images.json')));
        $images = $this->images->all();

        $this->assertCount(1, $images);
        $this->assertEquals(4711, $images[0]->id);
        $this->assertEquals('ubuntu-20.04', $images[0]->name);
        $this->assertLastRequestEquals('GET', '/images');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testUpdate()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/image.json')));
        $image = $this->images->get(4711);

        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/image.json')));
        $image->update(['name' => 'My new Image description', 'type' => 'snapshot']);
        $this->assertLastRequestEquals('PUT', '/images/4711');
        $this->assertLastRequestBodyParametersEqual(['name' => 'My new Image description', 'type' => 'snapshot']);
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testDelete()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/image.json')));
        $image = $this->images->get(4711);

        $this->mockHandler->append(new Response(204, []));
        $this->assertTrue($image->delete());
        $this->assertLastRequestEquals('DELETE', '/images/4711');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testChangeProtection()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/image.json')));
        $image = $this->images->get(4711);

        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/image_action_change_protection.json')));
        $apiResponse = $image->changeProtection();
        $this->assertEquals('change_protection', $apiResponse->action->command);
        $this->assertEquals($image->id, $apiResponse->action->resources[0]->id);
        $this->assertEquals('image', $apiResponse->action->resources[0]->type);

        $this->assertLastRequestEquals('POST', '/images/4711/actions/change_protection');
        $this->assertLastRequestBodyParametersEqual(['delete' => true]);
    }
}
