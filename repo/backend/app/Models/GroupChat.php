<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupChat extends Model
{
    protected $fillable = [
        'ride_order_id',
        'status',
        'disbanded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'disbanded_at' => 'datetime',
        ];
    }

    public function rideOrder(): BelongsTo
    {
        return $this->belongsTo(RideOrder::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GroupChatParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class);
    }
}
