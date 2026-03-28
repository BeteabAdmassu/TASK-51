<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'group_chat_id',
        'sender_id',
        'content',
        'type',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(GroupChat::class, 'group_chat_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function readReceipts(): HasMany
    {
        return $this->hasMany(MessageReadReceipt::class, 'message_id');
    }
}
