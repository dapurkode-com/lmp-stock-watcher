<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\User;
use App\Models\WatchlistStockCommodity;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class CommodityController extends Controller
{

    const WATCHABLE = "commodity";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = Auth::check() ? Auth::user() : User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $commodities = $this->selectedUser->watchlist(self::WATCHABLE)
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
        $user_id = $this->selectedUser->id;
        $query = Str::lower($request->input('query', ''));
        $commodityResources = DB::table('watchlist_stock_commodities')
            ->where(DB::raw('LOWER(name)'), 'like', "%$query%")
            ->whereRaw("watchlist_stock_commodities.id not in
            (select watchable_id from watchables where user_id = $user_id and watchable_type like '%WatchlistStockCommodity%')")
            ->get();

        return response()->json([
            'status' => true,
            'count' => $commodityResources->count(),
            'commodityResources' => SymbolResource::collection($commodityResources)
        ]);
    }

    /**
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function store(SymbolRequest $request): JsonResponse
    {

        $commodity = WatchlistStockCommodity::findOrFail($request->id);

        $this->selectedUser
            ->watchlist(self::WATCHABLE)
            ->syncWithoutDetaching([$commodity->id]);

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

        $this->selectedUser
            ->watchlist(self::WATCHABLE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
