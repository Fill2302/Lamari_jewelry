<?php

namespace App\Http\Controllers;

use App\Services\NovaPoshtaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NovaPoshtaController extends Controller
{
    public function cities(Request $request, NovaPoshtaService $novaPoshta): JsonResponse
    {
        $query = trim((string) $request->query('q'));
        abort_if(mb_strlen($query) < 2, 422, 'Введіть щонайменше 2 символи.');

        try {
            return response()->json(['data' => $novaPoshta->cities(mb_substr($query, 0, 80))]);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Не вдалося завантажити міста. Спробуйте ще раз.'], 503);
        }
    }

    public function warehouses(Request $request, NovaPoshtaService $novaPoshta): JsonResponse
    {
        $data = $request->validate([
            'city_ref' => ['required', 'uuid'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            return response()->json(['data' => $novaPoshta->warehouses($data['city_ref'], trim($data['q'] ?? ''))]);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Не вдалося завантажити відділення. Спробуйте ще раз.'], 503);
        }
    }
}
