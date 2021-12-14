<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolHoldRequest;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\User;
use App\Models\WatchlistStockCommodity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @package App\Http\Controllers\Api
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 *
 * @OA\Tag(name="Holdlist Commodity Controller")
 */
class HoldCommodityController extends Controller
{
    const HOLDABLE_TYPE = "commodity";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @OA\Get(
     *      path="/api/wallet/commodities",
     *      tags={"Holdlist Commodity Controller", "Holdlist Index"},
     *      summary="Collection of holdlist commodities raw data",
     *      operationId="holdlistCommodityIndex",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Collections of commodities",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", property="status"),
     *              @OA\Property(type="array", property="stocks", @OA\Items(
     *                  @OA\Property(property="id", type="integer", description="Id of collection", example=1),
     *                  @OA\Property(property="symbol", type="string", description="Symbol of stock", example="TSLA"),
     *                  @OA\Property(property="name", type="string", description="Name of stock", example="Tesla Inc"),
     *                  @OA\Property(property="prev_day_close_price", type="number", description="Previous close day price", example="26136251"),
     *                  @OA\Property(property="current_price", type="number", description="Current price", example="25666841"),
     *                  @OA\Property(property="change", type="number", description="Deviation between current and previous close day price", example="239983"),
     *                  @OA\Property(property="percent_change", type="number", description="Deviation in percent", example="0.94"),
     *                  @OA\Property(property="last_updated", type="date", description="Last data updated", example="2020-12-10 10:10:00"),
     *                  @OA\Property(property="amount", type="number", description="Amount of hold stock", example="5"),
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
        $commodities = $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'commodities' => StockResource::collection($commodities)
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/wallet/get-resource-commodity",
     *      tags={"Holdlist Commodity Controller", "Holdlist Get Resource"},
     *      summary="Commodities that avaiable to add on holdlist",
     *      operationId="holdlistCommoditiesGetResource",
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
     *          description="Collections of commodities",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", property="status"),
     *              @OA\Property(type="array", property="commodityResources", @OA\Items(
     *                  @OA\Property(property="id", type="integer", description="Id of collection", example=1),
     *                  @OA\Property(property="name", type="string", description="Name of stock", example="Gold"),
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
     * @OA\Post(
     *      path="/api/wallet/store-commodity",
     *      tags={"Holdlist Commodity Controller", "Holdlist Store"},
     *      summary="Add commodity to holdlist",
     *      operationId="holdlistCommoditiesStore",
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

        $commodity = WatchlistStockCommodity::findOrFail($request->id);

        $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->syncWithoutDetaching([$commodity->id => ['amount'=>$request->amount]]);

        return response()->json([
            'status' => true,
            'commodity' => StockResource::make($commodity)
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/wallet/remove-commodity",
     *      tags={"Holdlist Commodity Controller", "Holdlist Remove"},
     *      summary="Remove commodity from holdlist",
     *      operationId="holdlistCommoditiesRemove",
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
        $stock = WatchlistStockCommodity::findOrFail($request->id);

        $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
