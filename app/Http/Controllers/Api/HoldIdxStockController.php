<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolHoldRequest;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Http\Resources\SymbolResource;
use App\Models\User;
use App\Models\WatchlistStockIdx;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @package App\Http\Controllers\Api
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 *
 * @OA\Tag(name="Holdlist Idx Controller")
 */
class HoldIdxStockController extends Controller
{
    const HOLDABLE_TYPE = "idx-stock";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @OA\Get(
     *      path="/api/wallet/idx-stocks",
     *      tags={"Holdlist Idx Controller", "Holdlist Index"},
     *      summary="Collection of holdlist idx stocks raw data",
     *      operationId="holdlistIdxIndex",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Collections of idx stocks",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", property="status"),
     *              @OA\Property(type="array", property="stocks", @OA\Items(
     *                  @OA\Property(property="id", type="integer", description="Id of collection", example=1),
     *                  @OA\Property(property="symbol", type="string", description="Symbol of stock", example="BBRI"),
     *                  @OA\Property(property="name", type="string", description="Name of stock", example="Bank Rakyat Indonesia"),
     *                  @OA\Property(property="prev_day_close_price", type="number", description="Previous close day price", example="3200"),
     *                  @OA\Property(property="current_price", type="number", description="Current price", example="3400"),
     *                  @OA\Property(property="change", type="number", description="Deviation between current and previous close day price", example="200"),
     *                  @OA\Property(property="percent_change", type="number", description="Deviation in percent", example="8.1"),
     *                  @OA\Property(property="last_updated", type="date", description="Last data updated", example="2020-12-10 10:10:00"),
     *                  @OA\Property(property="amount", type="number", description="Amount of hold stock", example="8"),
     *                  @OA\Property(property="unit", type="number", description="Multiplier of stock and unit", example="100"),
     *              )),
     *          )
     *      ),
     * )
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $stocks = $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'stocks' => StockResource::collection($stocks)
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/wallet/get-resource-idx-stock",
     *      tags={"Holdlist Idx Controller", "Holdlist Get Resource"},
     *      summary="Idx stocks that avaiable to add on holdlist",
     *      operationId="holdlistIdxGetResource",
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
     *          description="Collections of stockResources",
     *          @OA\JsonContent(
     *              @OA\Property(type="boolean", property="status"),
     *              @OA\Property(type="array", property="stockResources", @OA\Items(
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
     * @OA\Post(
     *      path="/api/wallet/store-idx-stock",
     *      tags={"Holdlist Idx Controller", "Holdlist Store"},
     *      summary="Add idx stock to holdlist",
     *      operationId="holdlistIdxStore",
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

        $stock = WatchlistStockIdx::findOrFail($request->id);

        $this->selectedUser
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
     * @OA\Post(
     *      path="/api/wallet/remove-idx-stock",
     *      tags={"Holdlist Idx Controller", "Holdlist Remove"},
     *      summary="Remove idx stock from holdlist",
     *      operationId="holdlistIdxRemove",
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
        $stock = WatchlistStockIdx::findOrFail($request->id);

        $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
