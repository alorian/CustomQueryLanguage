<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndexRoute(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Custom Query Language. Demo App');
        self::assertSelectorExists('div#app');
    }

    public function testValidateRoute(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/validate');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('valid', $responseData);
        $this->assertIsBool($responseData['valid']);

        $this->assertArrayHasKey('suggestionsList', $responseData);
        $this->assertIsArray($responseData['suggestionsList']);

        $this->assertArrayHasKey('errorsList', $responseData);
        $this->assertIsArray($responseData['errorsList']);
    }

    public function invalidQueryProvider(): array
    {
        return [
            ['test', 1]
        ];
    }

    /**
     * @param string $query
     * @param int $caretPos
     *
     * @dataProvider invalidQueryProvider
     */
    public function testValidateRouteInvalid(string $query, int $caretPos): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/validate', [
            'query' => $query,
            'caretPos' => $caretPos
        ]);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('valid', $responseData);
        $this->assertIsBool($responseData['valid']);
        $this->assertFalse($responseData['valid']);

        $this->assertArrayHasKey('errorsList', $responseData);
        $this->assertIsArray($responseData['errorsList']);
        $this->assertNotEmpty($responseData['errorsList']);
    }

    public function validQueryProvider(): array
    {
        return [
            ['name = asd', 1],
            ['name ~ "project"', 1]
        ];
    }

    /**
     * @param string $query
     * @param int $caretPos
     *
     * @dataProvider validQueryProvider
     */
    public function testValidateRouteValid(string $query, int $caretPos): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/validate', [
            'query' => $query,
            'caretPos' => $caretPos
        ]);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('valid', $responseData);
        $this->assertIsBool($responseData['valid']);
        $this->assertTrue($responseData['valid']);

        $this->assertArrayHasKey('errorsList', $responseData);
        $this->assertIsArray($responseData['errorsList']);
        $this->assertEmpty($responseData['errorsList']);
    }

    public function testProjectsRoute(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/projects');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // QueryState
        $this->assertArrayHasKey('queryState', $responseData);
        $this->assertArrayHasKey('valid', $responseData['queryState']);
        $this->assertIsBool($responseData['queryState']['valid']);

        $this->assertArrayHasKey('suggestionsList', $responseData['queryState']);
        $this->assertIsArray($responseData['queryState']['suggestionsList']);

        $this->assertArrayHasKey('errorsList', $responseData['queryState']);
        $this->assertIsArray($responseData['queryState']['errorsList']);

        // ProjectsList
        $this->assertArrayHasKey('projectsList', $responseData);
        $this->assertIsArray($responseData['projectsList']);
        //$this->assertNotEmpty($responseData['projectsList']);
    }
}
