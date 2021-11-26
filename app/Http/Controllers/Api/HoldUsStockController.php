<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FinnhubHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolHoldRequest;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Models\WatchlistStockUs;
use Auth;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HoldUsStockController extends Controller
{
    const HOLDABLE_TYPE = "us-stock";

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $stocks = Auth::user()
            ->holdList(self::HOLDABLE_TYPE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => StockResource::collection($stocks)
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getResource(Request $request): JsonResponse
    {
        try {
            $content = FinnhubHelper::request('GET', '/search',
                [
                    'q' => $request->input('query', '')
                ]
            );

            $myUsStocks = Auth::user()
                ->holdList(self::HOLDABLE_TYPE)
                ->select('symbol')
                ->get();

            $myUsStockSymbols = $myUsStocks->pluck('symbol');

            $stockResources = [];
            foreach ($content['result'] as $resource){
                if(!$myUsStockSymbols->contains($resource['symbol'])){
                    array_push($stockResources, $resource);
                }
            }

            return response()->json([
                'status' => true,
                'count' => sizeof($stockResources),
                'stockResources' => $stockResources
            ]);

        } catch (BadResponseException | GuzzleException $e) {
            $response = $e->getResponse();
            return response()->json([
                'status' => false,
                'error_code' => $response->getStatusCode() ?? 503,
                'message' => 'Finnhub server error! - ' . $e->getMessage()
            ]);
        }
    }

    /**
     * @param SymbolHoldRequest $request
     * @return JsonResponse
     */
    public function store(SymbolHoldRequest $request): JsonResponse
    {

        $stock = WatchlistStockUs::firstOrCreate(['symbol' => $request->symbol], ['name' => $request->name]);

        Auth::user()
            ->holdList(self::HOLDABLE_TYPE)
            ->syncWithoutDetaching([
                $stock->id => ['amount' => $request->amount]
            ]);

        return response()->json([
            'status' => true,
            'data' => StockResource::make($stock)
        ]);
    }

    /**
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function remove(SymbolRequest $request): JsonResponse
    {
        $stock = WatchlistStockUs::findOrFail($request->id);

        Auth::user()
            ->holdList(self::HOLDABLE_TYPE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
