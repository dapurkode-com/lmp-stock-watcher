<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolHoldRequest;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\WatchlistStockIdx;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HoldIdxStockController extends Controller
{
    const HOLDABLE_TYPE = "idx-stock";

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
        $keyword = Str::lower($request->input('query', ''));
        $stockResources = DB::table('watchlist_stock_idxes')
            ->whereRaw("not exists (select holdable_id from holdables where user_id = $user_id and holdable_id = watchlist_stock_idxes.id and holdable_type like '%WatchlistStockIdx%')")
            ->where(function ($query) use ($keyword) {
                $query->where(DB::raw('LOWER(name)'), 'like', "%$keyword%")
                    ->orWhere(DB::raw('LOWER(symbol)'), 'like', "%$keyword%");
            })->get();

        return response()->json([
            'status' => true,
            'count' => $stockResources->count(),
            'stockResources' => SymbolResource::collection($stockResources)
        ]);
    }

    /**
     * @param SymbolHoldRequest $request
     * @return JsonResponse
     */
    public function store(SymbolHoldRequest $request): JsonResponse
    {

        $stock = WatchlistStockIdx::findOrFail($request->id);

        Auth::user()
            ->holdList(self::HOLDABLE_TYPE)
            ->syncWithoutDetaching([
                $stock->id => ['amount' => $request->amount, 'unit'=> 100]
            ]);

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
            ->holdList(self::HOLDABLE_TYPE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}