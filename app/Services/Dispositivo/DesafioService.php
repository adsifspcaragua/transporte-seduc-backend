<?php

namespace App\Services\Dispositivo;

use App\Enums\CodigoPresenca;
use App\Exceptions\PresencaNegada;
use App\Models\DesafioDispositivo;
use App\Models\Estudante;
use Illuminate\Support\Str;

/**
 * Emissao e consumo dos desafios que o navegador assina.
 *
 * O texto a assinar e montado so aqui, linha a linha, e vai pronto para o
 * cliente: ele assina os bytes UTF-8 exatamente como recebeu, sem remontar
 * JSON por conta propria. Na volta, o hash do texto devolvido precisa bater
 * com o guardado, e o desafio e consumido antes de a assinatura ser
 * conferida: cada desafio vale uma tentativa, certa ou errada.
 */
class DesafioService
{
    public const VERSAO = 1;

    public function __construct(private readonly AssinaturaEcdsa $assinatura) {}

    /**
     * @param  array{solicitacao_id?: int, dispositivo_id?: string, viagem_id?: int, veiculo_id?: int}  $vinculos
     * @return array{0: DesafioDispositivo, 1: string}
     */
    public function emitir(Estudante $estudante, string $proposito, array $vinculos): array
    {
        $id = (string) Str::uuid();
        $expiraEm = now()->addSeconds((int) config('presenca.desafio.validade_segundos'));
        $referencia = $proposito === DesafioDispositivo::VINCULACAO
            ? 'solicitacao:'.$vinculos['solicitacao_id']
            : 'viagem:'.$vinculos['viagem_id'];

        $texto = implode("\n", [
            'SIGTU',
            'v'.self::VERSAO,
            $proposito,
            $id,
            'estudante:'.$estudante->id,
            $referencia,
            rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='),
            (string) $expiraEm->getTimestamp(),
        ]);

        $desafio = DesafioDispositivo::create([
            'id' => $id,
            'estudante_id' => $estudante->id,
            'proposito' => $proposito,
            'versao' => self::VERSAO,
            'desafio_hash' => hash('sha256', $texto),
            'solicitacao_id' => $vinculos['solicitacao_id'] ?? null,
            'dispositivo_id' => $vinculos['dispositivo_id'] ?? null,
            'viagem_id' => $vinculos['viagem_id'] ?? null,
            'veiculo_id' => $vinculos['veiculo_id'] ?? null,
            'expira_em' => $expiraEm,
        ]);

        return [$desafio, $texto];
    }

    /**
     * Consome o desafio e confere a assinatura feita com a chave informada.
     *
     * @throws PresencaNegada
     */
    public function consumir(
        Estudante $estudante,
        string $proposito,
        string $desafioId,
        string $texto,
        string $assinaturaBase64,
        string $chaveDer,
    ): DesafioDispositivo {
        $desafio = Str::isUuid($desafioId)
            ? DesafioDispositivo::where('id', $desafioId)
                ->where('estudante_id', $estudante->id)
                ->where('proposito', $proposito)
                ->first()
            : null;

        // Desafio de outro estudante, de outro proposito ou com texto
        // alterado: para quem envia, tudo isso e so "prova invalida".
        if (! $desafio || ! hash_equals($desafio->desafio_hash, hash('sha256', $texto))) {
            throw new PresencaNegada(CodigoPresenca::DEVICE_PROOF_INVALID);
        }

        $consumido = DesafioDispositivo::whereKey($desafio->id)
            ->whereNull('usado_em')
            ->where('expira_em', '>', now())
            ->update(['usado_em' => now()]);

        if ($consumido !== 1) {
            throw new PresencaNegada($desafio->refresh()->usado_em !== null
                ? CodigoPresenca::CHALLENGE_ALREADY_USED
                : CodigoPresenca::CHALLENGE_EXPIRED);
        }

        if (! $this->assinatura->verificar($chaveDer, $texto, $assinaturaBase64)) {
            throw new PresencaNegada(CodigoPresenca::DEVICE_PROOF_INVALID);
        }

        return $desafio->refresh();
    }
}
