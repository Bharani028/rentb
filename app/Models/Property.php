<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit; // ✅ import the enum
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Property extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id','property_type_id','title','description','price','rent_type',
        'bedrooms','bathrooms','kitchen','balcony','hall','floors','parking',
        'area','door_no','street','district','city','state','country',
        'postal_code','phone_number','available_from','available_to','status','slug',
        'rejection_reason','approved_at','approved_by','view_count',
        'latitude','longitude',
    ];

    protected $casts = [
        'available_from' => 'date:d-m-Y',
        'available_to'   => 'date:d-m-Y',
        'approved_at'    => 'datetime',
        'view_count'     => 'integer',
        'price'          => 'decimal:2',
    ];

    protected $appends = ['cover_url'];

    /* --------------------------------
     | Media Library
     |-------------------------------*/
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images'); // add ->useDisk('public') if needed
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 560, 400) // ✅ enum, not string
            ->nonQueued();
    }

    public function getCoverUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('images', 'thumb')
            ?: $this->getFirstMediaUrl('images')
            ?: '';
    }

    /* --------------------------------
     | Relations
     |-------------------------------*/
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function type(): BelongsTo { return $this->belongsTo(PropertyType::class, 'property_type_id'); }

    public function propertyType(): BelongsTo { return $this->type(); }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenity');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
    protected function availableFrom(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
    );
}

protected function availableTo(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value ? Carbon::parse($value)->format('d-m-Y') : null,
    );
}
}
