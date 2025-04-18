<?php

namespace LKDev\Tests\Unit\Models\Firewalls;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\Models\Firewalls\Firewall;
use LKDev\HetznerCloud\Models\Firewalls\FirewallResource;
use LKDev\HetznerCloud\Models\Firewalls\FirewallRule;
use LKDev\HetznerCloud\Models\Firewalls\Firewalls;
use LKDev\HetznerCloud\Models\Servers\ServerReference;
use LKDev\Tests\TestCase;

/**
 * Class FirewallsTest.
 */
class FirewallsTest extends TestCase
{
    protected Firewalls $firewalls;

    public function setUp(): void
    {
        parent::setUp();
        $this->firewalls = new Firewalls($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/firewalls.json')));
        $firewalls = $this->firewalls->all();
        $this->assertCount(1, $firewalls);

        $this->assertLastRequestEquals('GET', '/firewalls');
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/firewalls.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $firewalls = $this->firewalls->list()->firewalls;
        $this->assertCount(1, $firewalls);
        $this->assertLastRequestEquals('GET', '/firewalls');
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/firewalls.json')));
        $firewall = $this->firewalls->getByName('Corporate Intranet Protection');
        $this->assertEquals(38, $firewall->id);
        $this->assertEquals('Corporate Intranet Protection', $firewall->name);

        $this->assertCount(1, $firewall->rules);
        $this->assertInstanceOf(FirewallRule::class, $firewall->rules[0]);
        $this->assertEquals(FirewallRule::DIRECTION_IN, $firewall->rules[0]->direction);
        $this->assertEquals(FirewallRule::PROTOCOL_TCP, $firewall->rules[0]->protocol);
        $this->assertEquals('80', $firewall->rules[0]->port);
        $this->assertCount(3, $firewall->rules[0]->source_ips);
        $this->assertCount(3, $firewall->rules[0]->destination_ips);

        $this->assertCount(1, $firewall->applied_to);
        $this->assertInstanceOf(FirewallResource::class, $firewall->applied_to[0]);

        $this->assertEmpty($firewall->labels);

        $this->assertLastRequestEquals('GET', '/firewalls');
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/firewall.json')));
        $firewall = $this->firewalls->get(38);
        $this->assertEquals(38, $firewall->id);
        $this->assertEquals('Corporate Intranet Protection', $firewall->name);

        $this->assertCount(1, $firewall->rules);
        $this->assertInstanceOf(FirewallRule::class, $firewall->rules[0]);
        $this->assertCount(1, $firewall->applied_to);
        $this->assertInstanceOf(FirewallResource::class, $firewall->applied_to[0]);

        $this->assertEmpty($firewall->labels);

        $this->assertLastRequestEquals('GET', '/firewalls/38');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testBasicCreate()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/firewall_create.json')));
        $resp = $this->firewalls->create('Corporate Intranet Protection');
        $this->assertInstanceOf(APIResponse::class, $resp);
        $this->assertInstanceOf(Firewall::class, $resp->getResponsePart('firewall'));
        $this->assertIsArray($resp->getResponsePart('actions'));

        $this->assertLastRequestEquals('POST', '/firewalls');
        $this->assertLastRequestBodyParametersEqual(['name' => 'Corporate Intranet Protection']);
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAdvancedCreate()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/firewall_create.json')));
        $resp = $this->firewalls->create('Corporate Intranet Protection', [new FirewallRule(FirewallRule::DIRECTION_IN, FirewallRule::PROTOCOL_TCP, ['127.0.0.1/32'], [], '80')], [new FirewallResource(FirewallResource::TYPE_SERVER, new ServerReference(5))]);
        $this->assertInstanceOf(APIResponse::class, $resp);
        $this->assertInstanceOf(Firewall::class, $resp->getResponsePart('firewall'));
        $this->assertIsArray($resp->getResponsePart('actions'));

        $this->assertLastRequestEquals('POST', '/firewalls');
        $this->assertLastRequestBodyParametersEqual(['name' => 'Corporate Intranet Protection', 'rules' => [['direction' => 'in', 'protocol' => 'tcp', 'source_ips' => ['127.0.0.1/32'], 'port' => '80']], 'apply_to' => [['type' => 'server', 'server' => ['id' => 5]]]]);
    }
}
