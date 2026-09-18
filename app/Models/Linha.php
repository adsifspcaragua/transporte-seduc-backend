<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Linha extends Model
{
    use HasFactory;

    protected $table = 'linhas';

    protected $fillable = [
        'name',
        'description',
        'departure_time',
        'return_time',
        'max_capacity',
        'motorista_id',
    ];

    public function estudantes()
    {
        return $this->hasMany(Estudante::class, 'linha_id');
    }

    public function motorista()
    {
        return $this->belongsTo(User::class, 'motorista_id');
    }

    public function chamadas()
    {
        return $this->hasMany(Chamada::class, 'linha_id');
    }
}
