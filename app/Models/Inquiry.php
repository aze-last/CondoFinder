<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'owner_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'message',
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
