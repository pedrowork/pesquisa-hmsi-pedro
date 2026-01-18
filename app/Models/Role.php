<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'guard_name',
    ];

    /**
     * Relacionamento many-to-many com usuários.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
