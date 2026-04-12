<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PaymentInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentInvoiceController extends Controller
{
    public function index(): View
    {
        $invoices = PaymentInvoice::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('backend.payment-invoices.index', compact('invoices'));
    }

    public function show(PaymentInvoice $paymentInvoice): View
    {
        $selectedStore = null;
        if (!empty($paymentInvoice->store_id)) {
            $selectedStore = DB::table('stores')->where('id', $paymentInvoice->store_id)->first();
        }

        $order = null;
        if (!empty($paymentInvoice->order_id)) {
            $order = DB::table('orders')->where('id', $paymentInvoice->order_id)->first();
        }

        $items = collect($paymentInvoice->items_json ?? [])->map(function (array $item) {
            return (object) $item;
        });

        $productIds = $items->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $imageMap = [];

        if ($productIds !== []) {
            $imageRows = DB::table('product_images')
                ->whereIn('product_id', $productIds)
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($imageRows as $imageRow) {
                $productId = (int) $imageRow->product_id;
                if (!array_key_exists($productId, $imageMap)) {
                    $imageMap[$productId] = $imageRow->image_url;
                }
            }
        }

        $items = $items->map(function ($item) use ($imageMap) {
            $item->preview_image_url = $imageMap[(int) ($item->product_id ?? 0)] ?? null;

            return $item;
        });

        return view('backend.payment-invoices.show', compact('paymentInvoice', 'selectedStore', 'order', 'items'));
    }
}
