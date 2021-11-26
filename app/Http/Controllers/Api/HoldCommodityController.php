<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolHoldRequest;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\WatchlistStockCommodity;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HoldCommodityController extends Controller
{
    const HOLDABLE_TYPE = "commodity";

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $commodities = Auth::user()
            ->holdList(self::HOLDABLE_TYPE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'commodities' => StockResource::collection($commodities)
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getResource(Request $request): JsonResponse
    {
        $user_id = auth()->user()->id;
        $query = Str::lower($request->input('query', ''));
        $commodityResources = DB::table('watchlist_stock_commodities')
            ->where(DB::raw('LOWER(name)'), 'like', "%$query%")
            ->whereRaw("watchlist_stock_commodities.id not in
            (select holdable_id from holdables where user_id = $user_id and holdable_type like '%WatchlistStockCommodity%')")
            ->get();

        return response()->json([
            'status' => true,
            'count' => $commodityResources->count(),
            'commodityResources' => SymbolResource::collection($commodityResources)
        ]);
    }

    /**
     * @param SymbolHoldRequest $request
     * @return JsonResponse
     */
    public function store(SymbolHoldRequest $request): JsonResponse
    {

        $commodity = WatchlistStockCommodity::findOrFail($request->id);

        Auth::user()
            ->holdList(self::HOLDABLE_TYPE)
            ->syncWithoutDetaching([$commodity->id => ['amount'=>$request->amount]]);

        return response()->json([
            'status' => true,
            'commodity' => StockResource::make($commodity)
        ]);
    }

    /**
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function remove(SymbolRequest $request):JsonResponse {
        $stock = WatchlistStockCommodity::findOrFail($request->id);

        Auth::user()
            ->holdList(self::HOLDABLE_TYPE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
