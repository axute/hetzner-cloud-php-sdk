<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\Actions;

use BadMethodCallException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Actions\Actions;
use LKDev\Tests\TestCase;

class ActionsTest extends TestCase
{
    protected Actions $actions;

    public function setUp(): void
    {
        parent::setUp();
        $this->actions = new Actions($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/action.json')));
        $datacenter = $this->actions->get(13);
        $this->assertEquals(13, $datacenter->id);
        $this->assertEquals('start_server', $datacenter->command);
        $this->assertLastRequestEquals('GET', '/actions/13');
    }

    public function testGetByName()
    {
        $this->expectException(BadMethodCallException::class);
        $this->actions->getByName('start_server');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/actions.json')));
        $actions = $this->actions->all();

        $this->assertCount(1, $actions);
        $this->assertEquals(13, $actions[0]->id);
        $this->assertEquals('start_server', $actions[0]->command);
        $this->assertLastRequestEquals('GET', '/actions');
    }
}
