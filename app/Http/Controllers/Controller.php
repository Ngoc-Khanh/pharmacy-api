<?php

namespace App\Http\Controllers;

use App\Ultis\HttpResponse;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Summary of json
     * @param mixed $data
     * @param string|array $message
     * @param int $status
     * @return JsonResponse
     */
    public function json(mixed $data = null, string|array $message = '', int $status = 200): JsonResponse
    {
        $locale = request()->getLocale();
        $result = HttpResponse::toJson($data, $message, $status, $locale);
        return response()->json($result, $status);
    }

    /**
     * Summary of fail
     * @param mixed $data
     * @param string|array $message
     * @param int $status
     * @return JsonResponse
     */
    public function fail(mixed $data = null, string|array $message = '', int $status = 400): JsonResponse
    {
        $locale = request()->getLocale();
        $result = HttpResponse::toJson($data, $message, $status, $locale);
        return response()->json($result, $status);
    }
}
