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

/**
 * @package App\Http\Controllers\Api
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 *
 * @OA\Tag(name="Holdlist Crypto Controller")
 */
class HoldCryptoController extends Controller
{
    const HOLDABLE_TYPE = "crypto";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @OA\Get(
     *      path="/api/wallet/cryptos",
     *      tags={"Holdlist Crypto Controller", "Holdlist Index"},
     *      summary="Collection of holdlist cryptos raw data",
     *      operationId="holdlistCryptoIndex",
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
     *                  @OA\Property(property="amount", type="number", description="Amount of hold stock", example="0.2"),
     *                  @OA\Property(property="unit", type="number", description="Multiplier of stock and unit", example="1"),
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
            ->holdList(self::HOLDABLE_TYPE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'cryptos' => StockResource::collection($cryptos)
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/wallet/get-resource-crypto",
     *      tags={"Holdlist Crypto Controller", "Holdlist Get Resource"},
     *      summary="Cryptocurrencies that avaiable to add on holdlist",
     *      operationId="holdlistCryptoGetResource",
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
     * @OA\Post(
     *      path="/api/wallet/store-crypto",
     *      tags={"Holdlist Crypto Controller", "Holdlist Store"},
     *      summary="Add cryptocurrency to holdlist",
     *      operationId="holdlistCryptoStore",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(type="number", property="id"),
     *              @OA\Property(type="number", property="amount")
     *          )
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
     * @OA\Post(
     *      path="/api/wallet/remove-crypto",
     *      tags={"Holdlist Crypto Controller", "Holdlist Remove"},
     *      summary="Remove crypto from holdlist",
     *      operationId="holdlistCryptoRemove",
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
