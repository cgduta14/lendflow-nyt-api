<?php

namespace App\Http\Controllers;

use App\Http\Requests\NytBestSellersListRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class NytBooksApiController extends Controller
{

    /**
     * @param NytBestSellersListRequest $request
     * @return JsonResponse
     */
    public function listBestSellersHistory(NytBestSellersListRequest $request): JsonResponse {
        $apiHost = Config::get('app.api.nyt_host');
        $apiKey = Config::get('app.api.nyt_key');

        if (!$apiHost) {
            return response()->json([
                'errors' => 'API host not found',
            ], 401);
        }

        if (!$apiKey) {
            return response()->json([
                'errors' => 'API key not found',
            ], 401);
        }

        $apiResponse = Http::get($apiHost . 'lists/best-sellers/history.json', array_merge(
            ['api-key' => $apiKey],
            $request->safe()->only('author', 'isbn', 'title', 'offset'),
        ));

        if ($apiResponse->status() === 401) {
            return response()->json([
                'errors' => $apiResponse->json('fault.faultstring'),
            ], 401);
        }

        return response()->json(
            [
                'num_results' => $apiResponse->json('num_results'),
                'data' => $apiResponse->collect('results'),
            ],
            200
        );
    }
}
