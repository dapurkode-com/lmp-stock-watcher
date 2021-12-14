<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\User;
use App\Models\WatchlistStockCrypto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @package App\Http\Controllers\Api
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 *
 * @OA\Tag(name="Watchlist Crypto Controller")
 */
class CryptoController extends Controller
{
    const WATCHABLE = "crypto";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @OA\Get(
     *      path="/api/watchlist/cryptos",
     *      tags={"Watchlist Crypto Controller", "Watchlist Index"},
     *      summary="Collection of watchlist cryptos raw data",
     *      operationId="watchlistCryptoIndex",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Collections of cryptos",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", property="status"),
     *              @OA\Property(type="array", property="cryptos", @OA\Items(
     *                  @OA\Property(property="id", type="integer", description="Id of collection", example=1),
     *                  @OA\Property(property="symbol", type="string", description="Symbol of stock", example="TSLA"),
     *                  @OA\Property(property="name", type="string", description="Name of stock", example="Tesla Inc"),
     *                  @OA\Property(property="prev_day_close_price", type="number", description="Previous close day price", example="26136251"),
     *                  @OA\Property(property="current_price", type="number", description="Current price", example="25666841"),
     *                  @OA\Property(property="change", type="number", description="Deviation between current and previous close day price", example="239983"),
     *                  @OA\Property(property="percent_change", type="number", description="Deviation in percent", example="0.94"),
     *                  @OA\Property(property="last_updated", type="date", description="Last data updated", example="2020-12-10 10:10:00"),
     *              )),
     *          )
     *      ),
     * )
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $cryptos = $this->selectedUser
            ->watchlist(self::WATCHABLE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'cryptos' => StockResource::collection($cryptos)
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/watchlist/get-resource-crypto",
     *      tags={"Watchlist Crypto Controller", "Watchlist Get Resource"},
     *      summary="Cryptocurrencies that avaiable to add on watchlist",
     *      operationId="watchlistCryptoGetResource",
     *
     *      @OA\Parameter(
     *          name="query",
     *          in="query",
     *          description="Searching keyword",
     *          allowEmptyValue=true,
     *          @OA\Schema(
     *              type="string"
     *         )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Collections of cryptoResources",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", property="status"),
     *              @OA\Property(type="array", property="cryptoResources", @OA\Items(
     *                  @OA\Property(property="id", type="integer", description="Id of collection", example=1),
     *                  @OA\Property(property="symbol", type="string", description="Symbol of stock", example="TSLA"),
     *                  @OA\Property(property="name", type="string", description="Name of stock", example="Tesla Inc"),
     *              )),
     *          )
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorize"
     *      )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getResource(Request $request): JsonResponse
    {
        $user_id = $this->selectedUser->id;
        $keyword = Str::lower($request->input('query', ''));
        $cryptoResources = DB::table('watchlist_stock_cryptos')
            ->whereRaw("not exists (select watchable_id from watchables where user_id = $user_id and watchable_id = watchlist_stock_cryptos.id and watchable_type like '%WatchlistStockCrypto%')")
            ->where(function ($query) use ($keyword) {
                $query->where(DB::raw('LOWER(name)'), 'like', "%$keyword%")
                    ->orWhere(DB::raw('LOWER(symbol)'), 'like', "%$keyword%");
            })
            ->get();

        return response()->json([
            'status' => true,
            'count' => $cryptoResources->count(),
            'cryptoResources' => SymbolResource::collection($cryptoResources)
        ]);

    }

    /**
     * @OA\Post(
     *      path="/api/watchlist/store-crypto",
     *      tags={"Watchlist Crypto Controller", "Watchlist Store"},
     *      summary="Add cryptocurrency to watchlist",
     *      operationId="watchlistCryptoStore",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(@OA\Property(type="number", property="id"))
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Error"
     *      ),
     * )
     *
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function store(SymbolRequest $request): JsonResponse
    {
        $crypto = WatchlistStockCrypto::findOrFail($request->id);

        $this->selectedUser
            ->watchlist(self::WATCHABLE)
            ->syncWithoutDetaching([$crypto->id]);

        return response()->json([
            'status' => true,
            'data' => StockResource::make($crypto)
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/watchlist/remove-crypto",
     *      tags={"Watchlist Crypto Controller", "Watchlist Remove"},
     *      summary="Remove crypto from watchlist",
     *      operationId="watchlistCryptoRemove",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(@OA\Property(type="number", property="id"))
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Error"
     *      ),
     * )
     *
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function remove(SymbolRequest $request): JsonResponse
    {
        $crypto = WatchlistStockCrypto::findOrFail($request->id);

        $this->selectedUser
            ->watchlist(self::WATCHABLE)
            ->detach([$crypto->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
