<?php

namespace App\Models;

use App\Models\Inscricao;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoDocumento extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscricao_id',
        'name',
        'type',
        'file_path',
        'status'
    ];

    public function inscricao()
    {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }

  
}
