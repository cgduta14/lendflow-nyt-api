<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NytBooksApiListBestSellersHistoryTest extends TestCase
{
    protected string $apiHost;

    protected string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = 'mockvalidkey';
        $this->apiHost = env('NYT_API_HOST') . 'lists/best-sellers/history.json';

        Config::set('app.api.nyt_key', $this->apiKey);

        Http::fake([
            $this->apiHost . '?api-key=invalidapikey' => Http::response(
                json_decode(file_get_contents('tests/stubs/nyt_best_sellers_list_no_api_key_status_401.json'), true),
                401
            ),
            $this->apiHost . '?api-key=mockvalidkey' => Http::response(
                json_decode(file_get_contents('tests/stubs/nyt_api_best_sellers_list_status_200.json'), true),
                200
            ),
            $this->apiHost . '?api-key=mockvalidkey&author=King' => Http::response(
                json_decode(file_get_contents('tests/stubs/nyt_api_best_sellers_list_author_status_200.json'), true),
                200
            ),
            $this->apiHost . '?api-key=mockvalidkey&isbn=9781982104351' => Http::response(
                json_decode(file_get_contents('tests/stubs/nyt_best_sellers_list_isbn_status_200.json'), true),
                200
            ),
            $this->apiHost . '?api-key=mockvalidkey&offset=60' => Http::response(
                json_decode(file_get_contents('tests/stubs/nyt_best_sellers_list_offset_status_200.json'), true),
                200
            ),
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Config::set('app.api.nyt_key', null);
    }

    #[Test] public function routeExists(): void
    {
        $response = $this->get(route('nyt.books.v3.list.bestsellers.history'));

        $response->assertOk();
        $response->assertJsonCount(2);
    }

    #[Test] public function listByAuthor(): void
    {
        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'), [
            'author' => 'King',
        ]);

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJson(['num_results' => 190]);
    }

    #[Test] public function apiHostConfigErrorNotSet(): void
    {
        Config::set('app.api.nyt_host', null);

        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'));

        $response->assertStatus(401);

        Config::set('app.api.nyt_host', $this->apiHost);
    }

    #[Test] public function apiKeyConfigErrorNotSet(): void
    {
        Config::set('app.api.nyt_key', null);

        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'));

        $response->assertStatus(401);

        Config::set('app.api.nyt_key', $this->apiKey);
    }

    #[Test] public function apiKeyNotValid(): void
    {
        Config::set('app.api.nyt_key', 'invalidapikey');

        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'));

        $response->assertStatus(401);
        $response->assertJson(['errors'=>'Failed to resolve API Key variable request.queryparam.api-key']);

        Config::set('app.api.nyt_key', $this->apiKey);
    }

    #[Test] public function listByIsbn(): void
    {
        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'), ['isbn' => '9781982104351']);

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJson(['num_results' => 1]);
    }

    #[Test] public function isbnValidation10Or13Digits(): void
    {
        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'), ['isbn' => '03991785700']);

        $response->assertJsonValidationErrors(['isbn']);
    }

    #[Test] public function isbnMustNotEndWithSemicolon(): void
    {
        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'), ['isbn' => '03991785700;']);

        $response->assertJsonValidationErrors(['isbn']);
    }

    #[Test] public function listWithOffset(): void
    {
        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'), ['offset' => '60']);

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJson(['num_results' => 36722]);
    }

    #[Test] public function offsetValidationNumeric(): void
    {
        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'), ['offset' => 'notnumeric']);

        $response->assertJsonValidationErrors(['offset']);
    }

    #[Test] public function offsetValidationDivisionBy20()
    {
        $response = $this->json('GET', route('nyt.books.v3.list.bestsellers.history'), ['offset' => 50]);

        $response->assertJsonValidationErrors(['offset']);
    }
}
