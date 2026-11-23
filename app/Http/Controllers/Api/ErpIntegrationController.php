<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class ErpIntegrationController extends Controller
{
    public function setBranchStatus(Request $request, string $branchId)
    {
        $data = $request->validate(['active' => ['required', 'boolean']]);
        $branch = Sucursal::query()->whereKey($branchId)->firstOrFail();
        $branch->setAttribute('situacion', $data['active'] ? 'A' : 'I');
        $branch->save();
        return response()->json([
            'id' => (string) $branch->getKey(),
            'active' => $branch->getRawOriginal('situacion') === 'A',
        ]);
    }
}
