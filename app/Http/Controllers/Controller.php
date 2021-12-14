<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="LMP Stock Watcher API Documentations",
 *    version="1.0.0",
 *      @OA\Contact(
 *          email="i.g.b.n.satyawibawa@gmail.com"
 *      ),
 * )
 *
 * @OA\Server(
 *      url=DAPURKODE_WEB,
 *      description="Dapurkode API Server"
 * )
 * @OA\Server(
 *      url=LOCALHOST,
 *      description="Local Testing Server"
 * )
 *
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
