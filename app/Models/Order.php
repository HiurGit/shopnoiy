<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    private const CUSTOMER_TRACKING_TOKEN_LENGTH = 24;

    protected $fillable = [
        'order_code',
        'customer_tracking_token',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_type',
        'store_id',
        'shipping_address_text',
        'payment_method',
        'order_status',
        'verified_at',
        'payment_status',
        'subtotal',
        'discount_amount',
        'promotion_id',
        'coupon_code',
        'shipping_fee',
        'total_amount',
        'note',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function getOrderStatusLabelAttribute(): string
    {
        return match ($this->order_status) {
            'pending_verification' => 'Chờ xác minh',
            'verified' => 'Đã xác minh',
            'processing' => 'Đang xử lý',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy',
            'pending' => 'Chờ xử lý',
            default => ucwords(str_replace('_', ' ', (string) $this->order_status)),
        };
    }

    public function isPendingVerification(): bool
    {
        return $this->order_status === 'pending_verification';
    }

    public static function generateCustomerTrackingToken(): string
    {
        do {
            $token = strtoupper(Str::random(self::CUSTOMER_TRACKING_TOKEN_LENGTH));
        } while (static::query()->where('customer_tracking_token', $token)->exists());

        return $token;
    }

    public function ensureCustomerTrackingToken(): string
    {
        if (filled($this->customer_tracking_token)) {
            return (string) $this->customer_tracking_token;
        }

        $token = static::generateCustomerTrackingToken();
        $this->forceFill(['customer_tracking_token' => $token])->save();

        return $token;
    }
}
