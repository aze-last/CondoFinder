<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Listing extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasSlug, InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
              ->width(200)
              ->height(200)
              ->sharpen(10);

        $this->addMediaConversion('large')
              ->width(1200)
              ->height(800);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('listings')
            ->useFallbackUrl('/images/placeholder-listing.jpg')
            ->useFallbackPath(public_path('/images/placeholder-listing.jpg'));
    }

    protected $fillable = [
        'owner_id',
        'title',
        'slug',
        'description',
        'price_per_night',
        'location_text',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function viewingRequests()
    {
        return $this->hasMany(ViewingRequest::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_listing');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'AVAILABLE');
    }
}
