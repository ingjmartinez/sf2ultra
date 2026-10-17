<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'ruta'];

    protected static function booted(): void
    {
        static::deleted(fn (Image $image) => Storage::disk('uploads')->delete($image->ruta));
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => Storage::disk('uploads')->url($this->ruta));
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}
