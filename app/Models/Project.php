<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['title', 'image', 'description', 'url', 'slug', 'is_public', 'type_id'];

    // Allocate the relation with types
    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
