<?php

namespace JobMetric\PackageCore\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * JobMetric\PackageCore\Controllers\HasResponse
 */
trait HasResponse
{
    /**
     * response json
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     *
     * @return JsonResponse
     */
    public function response(mixed $data = [], string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * response json with additional
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $status
     * @param array $additional
     *
     * @return JsonResponse
     */
    public function responseWithAdditional(mixed $data = [], string $message = null, int $status = 200, array $additional = []): JsonResponse
    {
        return $data->additional(array_merge([
            'message' => $message ?? ''
        ], $additional))->response()->setStatusCode($status);
    }
}
