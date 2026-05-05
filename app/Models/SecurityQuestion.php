<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class SecurityQuestion extends Model
{
    protected $fillable = [
        'user_id',
        'question',
        'answer',
    ];

    protected $hidden = [
        'answer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setAnswerAttribute(string $value): void
    {
        $this->attributes['answer'] = Hash::make($value);
    }

    public function checkAnswer(string $value): bool
    {
        return Hash::check($value, $this->answer);
    }
}
