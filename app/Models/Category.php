<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->usingSeparator('-')
            ->allowDuplicateSlugs(); // Duplicates allowed across different owners? Actually slug should be unique per owner usually, but standard sluggable is global. We might need customized slugging or just rely on global uniqueness. Let's stick to global for now or append ID.
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function listings()
    {
        return $this->belongsToMany(Listing::class, 'category_listing');
    }
}
