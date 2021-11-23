<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\WatchlistStockIdx;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IdxStockController extends Controller
{
    const WATCHABLE = "idx-stock";

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $stocks = Auth::user()
            ->watchlist(self::WATCHABLE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'stocks' => StockResource::collection($stocks)
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
        $stockResources = DB::table('watchlist_stock_idxes')
            ->where(DB::raw('LOWER(name)'), 'like', "%$query%")
            ->orWhere(DB::raw('LOWER(symbol)'), 'like', "%$query%")
            ->whereRaw("watchlist_stock_idxes.id not in
            (select watchable_id from watchables where user_id = $user_id and watchable_type like '%WatchlistStockIdx%')")
            ->get();

        return response()->json([
            'status' => true,
            'count' => $stockResources->count(),
            'stockResources' => SymbolResource::collection($stockResources)
        ]);
    }

    /**
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function store(SymbolRequest $request): JsonResponse
    {

        $stock = WatchlistStockIdx::findOrFail($request->id);

        Auth::user()
            ->watchlist(self::WATCHABLE)
            ->syncWithoutDetaching([$stock->id]);

        return response()->json([
            'status' => true,
            'stock' => StockResource::make($stock)
        ]);
    }

    /**
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function remove(SymbolRequest $request):JsonResponse {
        $stock = WatchlistStockIdx::findOrFail($request->id);

        Auth::user()
            ->watchlist(self::WATCHABLE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
