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
     * @param array $data
     * @param string|null $message
     * @param int|null $status
     * @param array $additional
     *
     * @return JsonResponse
     */
    public function response(array $data = [], string $message = null, int $status = null, array $additional = []): JsonResponse
    {
        $message = $message ?? $data['message'] ?? '';
        $status = $status ?? $data['status'] ?? 200;

        return response()->json(array_merge($data, [
            'message' => $message
        ], $additional), $status);
    }

    /**
     * response collection json
     *
     * @param $data
     * @param string|null $message
     * @param int|null $status
     * @param array $additional
     *
     * @return JsonResponse
     */
    public function responseCollection($data, string $message = null, int $status = null, array $additional = []): JsonResponse
    {
        $message = $message ?? $data['message'] ?? '';
        $status = $status ?? $data['status'] ?? 200;

        return $data->additional(array_merge([
            'message' => $message
        ], $additional))->response()->setStatusCode($status);
    }
}
