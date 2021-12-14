<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FinnhubHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Models\User;
use App\Models\WatchlistStockUs;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @package App\Http\Controllers\Api
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 *
 * @OA\Tag(name="Watchlist Us Controller")
 */
class UsStockController extends Controller
{

    const WATCHABLE = "us-stock";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @OA\Get(
     *      path="/api/watchlist/us-stocks",
     *      tags={"Watchlist Us Controller", "Watchlist Index"},
     *      summary="Collection of watchlist us stocks raw data",
     *      operationId="watchlistUsIndex",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Collections of us stocks",
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
            ->watchlist(self::WATCHABLE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'stocks' => StockResource::collection($stocks)
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/watchlist/get-resource-us-stock",
     *      tags={"Watchlist Us Controller", "Watchlist Get Resource"},
     *      summary="Us stocks that avaiable to add on watchlist",
     *      operationId="watchlistUsGetResource",
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
        try {
            $content = FinnhubHelper::request('GET', '/search',
                [
                    'q' => $request->input('query', '')
                ]
            );

            $myUsStocks = $this->selectedUser
                ->watchlist(self::WATCHABLE)
                ->get();

            $myUsStockSymbols = $myUsStocks->pluck('symbol');

            $stockResources = [];
            foreach ($content['result'] as $resource){
                if(!$myUsStockSymbols->contains($resource['symbol'])){
                    array_push($stockResources, $resource);
                }
            }

            return response()->json([
                'status' => true,
                'count' => sizeof($stockResources),
                'stockResources' => $stockResources
            ]);

        } catch (BadResponseException | GuzzleException $e) {
            $response = $e->getResponse();
            return response()->json([
                'status' => false,
                'error_code' => $response->getStatusCode() ?? 503,
                'message' => 'Finnhub server error! - ' . $e->getMessage()
            ]);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/watchlist/store-us-stock",
     *      tags={"Watchlist Us Controller", "Watchlist Store"},
     *      summary="Add us stock to watchlist",
     *      operationId="watchlistUsStore",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(type="string", property="symbol"),
     *              @OA\Property(type="string", property="name"),
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
     * @param SymbolRequest $request
     * @return JsonResponse
     */
    public function store(SymbolRequest $request): JsonResponse
    {

        $stock = WatchlistStockUs::firstOrCreate(['symbol' => $request->symbol], ['name' => $request->name]);

        $this->selectedUser
            ->watchlist(self::WATCHABLE)
            ->syncWithoutDetaching([$stock->id]);

        return response()->json([
            'status' => true,
            'data' => StockResource::make($stock)
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/watchlist/remove-us-stock",
     *      tags={"Watchlist Us Controller", "Watchlist Remove"},
     *      summary="Remove us stock from watchlist",
     *      operationId="watchlistUsRemove",
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
        $stock = WatchlistStockUs::findOrFail($request->id);

        $this->selectedUser
            ->watchlist(self::WATCHABLE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
