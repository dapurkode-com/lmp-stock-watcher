<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
 * @OA\Tag(name="Watchlist Commodity Controller")
 */
class CommodityController extends Controller
{

    const WATCHABLE = "commodity";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @OA\Get(
     *      path="/api/watchlist/commodities",
     *      tags={"Watchlist Commodity Controller", "Watchlist Index"},
     *      summary="Collection of watchlist commodities raw data",
     *      operationId="watchlistCommoditiesIndex",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Collections of commodities",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", property="status"),
     *              @OA\Property(type="array", property="commodities", @OA\Items(
     *                  @OA\Property(property="id", type="integer", description="Id of collection", example=1),
     *                  @OA\Property(property="name", type="string", description="Name of stock", example="Gold"),
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
        $commodities = $this->selectedUser->watchlist(self::WATCHABLE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'commodities' => StockResource::collection($commodities)
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/watchlist/get-resource-commodity",
     *      tags={"Watchlist Commodity Controller", "Watchlist Get Resource"},
     *      summary="Commodities that avaiable to add on watchlist",
     *      operationId="watchlistCommoditiesGetResource",
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
            (select watchable_id from watchables where user_id = $user_id and watchable_type like '%WatchlistStockCommodity%')")
            ->get();

        return response()->json([
            'status' => true,
            'count' => $commodityResources->count(),
            'commodityResources' => SymbolResource::collection($commodityResources)
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/watchlist/store-commodity",
     *      tags={"Watchlist Commodity Controller", "Watchlist Store"},
     *      summary="Add commodity to watchlist",
     *      operationId="watchlistCommoditiesStore",
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
     * @OA\Post(
     *      path="/api/watchlist/remove-commodity",
     *      tags={"Watchlist Commodity Controller", "Watchlist Remove"},
     *      summary="Remove commodity from watchlist",
     *      operationId="watchlistCommoditiesRemove",
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
            ->watchlist(self::WATCHABLE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
