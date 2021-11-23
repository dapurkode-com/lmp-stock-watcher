<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\WatchlistStockCrypto;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CryptoController extends Controller
{
    const WATCHABLE = "crypto";

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $cryptos = Auth::user()
            ->watchlist(self::WATCHABLE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => StockResource::collection($cryptos)
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
        $cryptoResources = DB::table('watchlist_stock_cryptos')
            ->where(DB::raw('LOWER(name)'), 'like', "%$query%")
            ->orWhere(DB::raw('LOWER(symbol)'), 'like', "%$query%")
            ->whereRaw("watchlist_stock_cryptos.id not in
            (select watchable_id from watchables where user_id = $user_id and watchable_type like '%WatchlistStockCrypto%')")
            ->get();

        return response()->json([
            'status' => true,
            'count' => $cryptoResources->count(),
            'cryptoResources' => SymbolResource::collection($cryptoResources)
        ]);

    }

    /**
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function store(SymbolRequest $request): JsonResponse
    {
        $crypto = WatchlistStockCrypto::findOrFail($request->id);

        Auth::user()
            ->watchlist(self::WATCHABLE)
            ->syncWithoutDetaching([$crypto->id]);

        return response()->json([
            'status' => true,
            'data' => StockResource::make($crypto)
        ]);
    }

    /**
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function remove(SymbolRequest $request): JsonResponse
    {
        $crypto = WatchlistStockCrypto::findOrFail($request->id);

        Auth::user()
            ->watchlist(self::WATCHABLE)
            ->detach([$crypto->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
