<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineDispensingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispensing_id',
        'medicine_id',
        'batch_id',
        'quantity',
        'dosage_instruction',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function dispensing(): BelongsTo
    {
        return $this->belongsTo(MedicineDispensing::class, 'dispensing_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }
}
