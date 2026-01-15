<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
	use HasFactory;

	protected $fillable = [
		'title',
		'description',
		'category',
		'price',
		'image',
		'user_id',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Retourne l'URL complète de l'image si disponible.
	 */
	public function getImageAttribute($value)
	{
		if (!$value) return null;
		// si la valeur stockée est déjà une URL absolue, la retourner telle quelle
		if (preg_match('#^https?://#i', $value)) {
			return $value;
		}
		// sinon construire l'URL à partir du disque "public" afin d'avoir toujours une URL absolue valide
		try {
			return Storage::disk('public')->url($value);
		} catch (\Throwable $e) {
			// fallback: utiliser helper url() si Storage échoue
			return url($value);
		}
	}
}
