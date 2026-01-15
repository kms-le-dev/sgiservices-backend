<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
	use HasFactory;

	protected $fillable = [
		'title',
		'url',
		'category',
		'description',
		'user_id',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Retourne l'URL absolue de la ressource média
	 */
	public function getUrlAttribute($value)
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
