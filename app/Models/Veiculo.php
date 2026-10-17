<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A van fisica. O QR Code fixo nela leva a este registro.
 *
 * O token do QR so existe em claro no momento em que e gerado; aqui fica o
 * hash. Ele identifica a van, mas nao autoriza presenca sozinho: pode ser
 * fotografado e, na pratica, e publico.
 */
class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'veiculos';

    protected $fillable = [
        'identificacao',
        'placa',
        'ativo',
    ];

    protected $hidden = [
        'qr_token_hash',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'qr_gerado_em' => 'datetime',
    ];

    public function atribuicoes()
    {
        return $this->hasMany(AtribuicaoVeiculo::class, 'veiculo_id');
    }

    public function viagens()
    {
        return $this->hasMany(Viagem::class, 'veiculo_id');
    }

    public static function hashDoToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function peloToken(string $token): ?self
    {
        return static::where('qr_token_hash', static::hashDoToken($token))->first();
    }

    /**
     * Gera um token novo e devolve-o em claro, para impressao. O anterior,
     * se houver, deixa de valer na hora.
     */
    public function gerarTokenQr(): string
    {
        // 32 bytes do CSPRNG em base64url: opaco, nao sequencial, sem "=".
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        $this->forceFill([
            'qr_token_hash' => static::hashDoToken($token),
            'qr_gerado_em' => now(),
        ])->save();

        return $token;
    }

    public static function urlDoQr(string $token): string
    {
        return config('presenca.qr_url').'?t='.$token;
    }
}
