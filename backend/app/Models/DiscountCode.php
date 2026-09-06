<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'expires_at' => 'datetime', 'is_active' => 'boolean', 'conditions' => 'array'];
    }

    public function calculate(int $subtotal): int
    {
        $raw = $this->type === 'percent' ? (int) floor($subtotal * $this->value / 100) : $this->value;

        return (int) min($raw, $this->max_discount ?? $raw, $subtotal);
    }
}
