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
        // S'assurer que c'est un chemin relatif et le convertir en URL publique
        $basePath = rtrim(config('app.url'), '/');
        return $basePath . '/storage/' . ltrim($value, '/');
    }
}
