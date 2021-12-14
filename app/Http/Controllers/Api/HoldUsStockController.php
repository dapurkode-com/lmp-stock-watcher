<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FinnhubHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolHoldRequest;
use App\Http\Requests\SymbolRequest;
use App\Http\Resources\StockResource;
use App\Models\User;
use App\Models\WatchlistStockUs;
use Auth;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @package App\Http\Controllers\Api
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 *
 * @OA\Tag(name="Holdlist Us Controller")
 */
class HoldUsStockController extends Controller
{
    const HOLDABLE_TYPE = "us-stock";

    private User $selectedUser;

    public function __construct(){
        $this->selectedUser = User::email(config('app.admin_email'))->firstOrFail();
    }

    /**
     * @OA\Get(
     *      path="/api/wallet/us-stocks",
     *      tags={"Holdlist Us Controller", "Holdlist Index"},
     *      summary="Collection of holdlist us stocks raw data",
     *      operationId="holdlistUsIndex",
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
     *                  @OA\Property(property="amount", type="number", description="Amount of hold stock", example="2"),
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
        $stocks = $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => StockResource::collection($stocks)
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/wallet/get-resource-us-stock",
     *      tags={"Holdlist Us Controller", "Holdlist Get Resource"},
     *      summary="Us stocks that avaiable to add on holdlist",
     *      operationId="holdlistUsGetResource",
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
                ->holdList(self::HOLDABLE_TYPE)
                ->select('symbol')
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
     *      path="/api/wallet/store-us-stock",
     *      tags={"Holdlist Us Controller", "Holdlist Store"},
     *      summary="Add us stock to holdlist",
     *      operationId="holdlistUsStore",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(type="string", property="symbol"),
     *              @OA\Property(type="string", property="name"),
     *              @OA\Property(type="number", property="amount"),
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

        $stock = WatchlistStockUs::firstOrCreate(['symbol' => $request->symbol], ['name' => $request->name]);

        $this->selectedUser
            ->holdList(self::HOLDABLE_TYPE)
            ->syncWithoutDetaching([
                $stock->id => ['amount' => $request->amount]
            ]);

        return response()->json([
            'status' => true,
            'data' => StockResource::make($stock)
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/wallet/remove-us-stock",
     *      tags={"Holdlist Us Controller", "Holdlist Remove"},
     *      summary="Remove us stock from holdlist",
     *      operationId="holdlistUsRemove",
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
            ->holdList(self::HOLDABLE_TYPE)
            ->detach([$stock->id]);

        return response()->json([
            'status' => true,
        ]);
    }
}
