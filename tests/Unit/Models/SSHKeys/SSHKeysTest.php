<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\SSHKeys;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\SSHKeys\SSHKeys;
use LKDev\Tests\TestCase;

class SSHKeysTest extends TestCase
{
    protected SSHKeys $ssh_keys;

    public function setUp(): void
    {
        parent::setUp();
        $this->ssh_keys = new SSHKeys($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/ssh_key.json')));
        $ssh_key = $this->ssh_keys->get(2323);
        $this->assertEquals(2323, $ssh_key->id);
        $this->assertEquals('My ssh key', $ssh_key->name);
        $this->assertEquals('ssh-rsa AAAjjk76kgf...Xt', $ssh_key->public_key);

        $this->assertLastRequestEquals('GET', '/ssh_keys/2323');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/ssh_keys.json')));
        $ssh_key = $this->ssh_keys->getByName('My ssh key');
        $this->assertEquals(2323, $ssh_key->id);
        $this->assertEquals('My ssh key', $ssh_key->name);
        $this->assertEquals('ssh-rsa AAAjjk76kgf...Xt', $ssh_key->public_key);

        $this->assertLastRequestEquals('GET', '/ssh_keys');
        $this->assertLastRequestQueryParametersContains('name', 'My ssh key');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/ssh_keys.json')));
        $ssh_keys = $this->ssh_keys->all();

        $this->assertCount(1, $ssh_keys);
        $this->assertEquals(2323, $ssh_keys[0]->id);
        $this->assertEquals('My ssh key', $ssh_keys[0]->name);
        $this->assertEquals('ssh-rsa AAAjjk76kgf...Xt', $ssh_keys[0]->public_key);

        $this->assertLastRequestEquals('GET', '/ssh_keys');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/ssh_keys.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $ssh_keys = $this->ssh_keys->list()->ssh_keys;

        $this->assertCount(1, $ssh_keys);
        $this->assertEquals(2323, $ssh_keys[0]->id);
        $this->assertEquals('My ssh key', $ssh_keys[0]->name);
        $this->assertEquals('ssh-rsa AAAjjk76kgf...Xt', $ssh_keys[0]->public_key);

        $this->assertLastRequestEquals('GET', '/ssh_keys');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testCreate()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/ssh_key.json')));

        $this->ssh_keys->create('my ssh key', 'ssh-rsa AAAjjk76kgf...Xt');

        $this->assertLastRequestEquals('POST', '/ssh_keys');
        $this->assertLastRequestBodyParametersEqual(['name' => 'my ssh key', 'public_key' => 'ssh-rsa AAAjjk76kgf...Xt']);
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testDelete()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/ssh_key.json')));

        $sshKey = $this->ssh_keys->get(2323);
        $this->mockHandler->append(new Response(204, []));
        $this->assertTrue($sshKey->delete());
        $this->assertLastRequestEquals('DELETE', '/ssh_keys/2323');
    }
}
