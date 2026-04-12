<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        $stores = Store::query()->orderBy('priority_order')->orderBy('id')->paginate(20);

        return view('backend.stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('backend.stores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Store::create([
            'code' => $request->input('code', 'STORE-' . time()),
            'name' => $request->input('name', 'Cửa hàng mới'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'province' => $request->input('province', ''),
            'district' => $request->input('district', ''),
            'ward' => $request->input('ward'),
            'address_line' => $request->input('address_line', ''),
            'open_time' => $request->input('open_time'),
            'close_time' => $request->input('close_time'),
            'pickup_enabled' => $request->boolean('pickup_enabled'),
            'priority_order' => (int) $request->input('priority_order', 0),
            'status' => $request->input('status', 'active'),
        ]);

        return redirect()->route('backend.stores')->with('success', 'Đã tạo cửa hàng.');
    }

    public function show(Store $store): View
    {
        $hours = DB::table('store_business_hours')->where('store_id', $store->id)->orderBy('weekday')->get();

        return view('backend.stores.show', compact('store', 'hours'));
    }

    public function edit(Store $store): View
    {
        return view('backend.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $store->update([
            'code' => $request->input('code', $store->code),
            'name' => $request->input('name', $store->name),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'province' => $request->input('province', $store->province),
            'district' => $request->input('district', $store->district),
            'ward' => $request->input('ward'),
            'address_line' => $request->input('address_line', $store->address_line),
            'open_time' => $request->input('open_time'),
            'close_time' => $request->input('close_time'),
            'pickup_enabled' => $request->boolean('pickup_enabled'),
            'priority_order' => (int) $request->input('priority_order', $store->priority_order),
            'status' => $request->input('status', $store->status),
        ]);

        return redirect()->route('backend.stores')->with('success', 'Đã cập nhật cửa hàng.');
    }

    public function destroy(Request $request, Store $store): RedirectResponse|JsonResponse
    {
        DB::table('store_business_hours')->where('store_id', $store->id)->delete();
        DB::table('inventories')->where('store_id', $store->id)->update(['store_id' => null]);
        DB::table('orders')->where('store_id', $store->id)->update(['store_id' => null]);
        $store->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa cửa hàng.',
                'store_id' => $store->id,
            ]);
        }

        return redirect()->route('backend.stores')->with('success', 'Đã xóa cửa hàng.');
    }
}
