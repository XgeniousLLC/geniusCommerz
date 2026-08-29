<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    /** Product tax classes. Reduced and zero exist because EU rates differ by goods type. */
    public const CLASSES = ['standard', 'reduced', 'zero'];

    protected $fillable = ['tax_zone_id', 'name', 'tax_class', 'rate', 'applies_to_shipping', 'priority'];

    protected $casts = [
        'rate'                => 'decimal:4',
        'applies_to_shipping' => 'boolean',
        'priority'            => 'integer',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(TaxZone::class, 'tax_zone_id');
    }

    /** Rate as a multiplier, e.g. 20.0000 => 0.20 */
    public function fraction(): float
    {
        return (float) $this->rate / 100;
    }
}
