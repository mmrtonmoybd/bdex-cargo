<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * An external customer. Guest bookings are allowed, so an order may have none.
 *
 * @property array<int, string>|null $notification_channels
 */
final class Customer extends Authenticatable
{
    /** @use HasFactory<CustomerFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token'];

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Channels this customer agreed to hear from us on.
     *
     * Mail is the floor: a shipment with no reachable channel is worse than one
     * more email, and every channel beyond it is opt-in.
     *
     * @return array<int, string>
     */
    public function channels(): array
    {
        $channels = $this->notification_channels ?? [];

        return $channels === [] ? ['mail'] : $channels;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'notification_channels' => 'array',
            'is_business' => 'boolean',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
