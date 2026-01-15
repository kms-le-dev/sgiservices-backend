<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'featured_image',
        'published_at',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retourne l'URL publique de `featured_image` ou la valeur si c'est déjà une URL absolue.
     */
    public function getFeaturedImageAttribute($value)
    {
        if (!$value) return null;
        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }
        try {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($value);
        } catch (\Throwable $e) {
            return url($value);
        }
    }
}
