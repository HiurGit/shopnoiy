<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_code',
        'order_id',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_type',
        'store_id',
        'shipping_address_text',
        'payment_method',
        'invoice_status',
        'payment_status',
        'subtotal',
        'discount_amount',
        'shipping_fee',
        'total_amount',
        'transfer_content',
        'note',
        'items_json',
        'raw_payment_json',
        'paid_at',
        'converted_at',
    ];

    protected $casts = [
        'items_json' => 'array',
        'raw_payment_json' => 'array',
        'paid_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ((string) $this->payment_method) {
            'cod' => 'Thanh toán khi nhận hàng',
            'vietqr' => 'Chuyển khoản VietQR',
            default => ucwords(str_replace('_', ' ', (string) $this->payment_method)),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ((string) $this->payment_status) {
            'unpaid' => 'Chưa thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'refunded' => 'Đã hoàn tiền',
            default => ucwords(str_replace('_', ' ', (string) $this->payment_status)),
        };
    }

    public function getInvoiceStatusLabelAttribute(): string
    {
        return match ((string) $this->invoice_status) {
            'pending_payment' => 'Chờ thanh toán',
            'completed' => 'Hoàn tất',
            'expired' => 'Hết hạn',
            'cancelled' => 'Đã hủy',
            default => ucwords(str_replace('_', ' ', (string) $this->invoice_status)),
        };
    }

    public function getDeliveryTypeLabelAttribute(): string
    {
        return match ((string) $this->delivery_type) {
            'delivery' => 'Giao tận nơi',
            'pickup' => 'Nhận tại cửa hàng',
            default => ucwords(str_replace('_', ' ', (string) $this->delivery_type)),
        };
    }
}
