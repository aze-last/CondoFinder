<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'owner_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'preferred_datetime',
        'status',
    ];

    protected $casts = [
        'preferred_datetime' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class)->withTrashed();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
