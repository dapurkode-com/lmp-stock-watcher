<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolHoldRequest;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\User;
use App\Models\WatchlistStockCrypto;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HoldCryptoController extends Controller
{
    const HOLDABLE_TYPE = "crypto";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = Auth::check() ? Auth::user() : User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $cryptos = $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'cryptos' => StockResource::collection($cryptos)
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getResource(Request $request): JsonResponse
    {
        $user_id = $this->selectedUser->id;
        $keyword = Str::lower($request->input('query', ''));
        $cryptoResources = DB::table('watchlist_stock_cryptos')
            ->whereRaw("not exists (select holdable_id from holdables where user_id = $user_id and holdable_id = watchlist_stock_cryptos.id and holdable_type like '%WatchlistStockCrypto%')")
            ->where(function ($query) use ($keyword) {
                $query->where(DB::raw('LOWER(name)'), 'like', "%$keyword%")
                    ->orWhere(DB::raw('LOWER(symbol)'), 'like', "%$keyword%");
            })->get();

        return response()->json([
            'status' => true,
            'count' => $cryptoResources->count(),
            'cryptoResources' => SymbolResource::collection($cryptoResources)
        ]);
    }

    /**
     * @param SymbolHoldRequest $request
     * @return JsonResponse
     */
    public function store(SymbolHoldRequest $request): JsonResponse
    {

        $crypto = WatchlistStockCrypto::findOrFail($request->id);

        $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->syncWithoutDetaching([
                $crypto->id => ['amount' => $request->amount]
            ]);

        return response()->json([
            'status' => true,
            'crypto' => StockResource::make($crypto)
        ]);
    }

    /**
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function remove(SymbolRequest $request):JsonResponse {
        $crypto = WatchlistStockCrypto::findOrFail($request->id);

        $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->detach([$crypto->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
