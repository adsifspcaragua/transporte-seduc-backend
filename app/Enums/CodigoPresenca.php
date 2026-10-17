<?php

namespace App\Enums;

/**
 * Motivos controlados de recusa no fluxo de presenca e de dispositivo.
 *
 * O codigo vai na resposta (`codigo`) para o frontend decidir a orientacao;
 * a mensagem ja sai pronta para o estudante. Nenhuma delas diz se uma conta,
 * chave ou van existe alem do necessario para corrigir a tentativa.
 */
enum CodigoPresenca: string
{
    case STUDENT_NOT_ELIGIBLE = 'STUDENT_NOT_ELIGIBLE';
    case DEVICE_NOT_ENROLLED = 'DEVICE_NOT_ENROLLED';
    case DEVICE_PENDING_APPROVAL = 'DEVICE_PENDING_APPROVAL';
    case DEVICE_REVOKED = 'DEVICE_REVOKED';
    case DEVICE_PROOF_INVALID = 'DEVICE_PROOF_INVALID';
    case QR_INVALID = 'QR_INVALID';
    case NO_MATCHING_TRIP = 'NO_MATCHING_TRIP';
    case LINE_NOT_AUTHORIZED = 'LINE_NOT_AUTHORIZED';
    case OUTSIDE_TIME_WINDOW = 'OUTSIDE_TIME_WINDOW';
    case LOCATION_PERMISSION_DENIED = 'LOCATION_PERMISSION_DENIED';
    case LOCATION_UNAVAILABLE = 'LOCATION_UNAVAILABLE';
    case LOCATION_INACCURATE = 'LOCATION_INACCURATE';
    case OUTSIDE_GEOFENCE = 'OUTSIDE_GEOFENCE';
    case CHALLENGE_EXPIRED = 'CHALLENGE_EXPIRED';
    case CHALLENGE_ALREADY_USED = 'CHALLENGE_ALREADY_USED';
    case ATTENDANCE_ALREADY_RECORDED = 'ATTENDANCE_ALREADY_RECORDED';
    case MANUAL_REVIEW_REQUIRED = 'MANUAL_REVIEW_REQUIRED';

    public function status(): int
    {
        return match ($this) {
            self::STUDENT_NOT_ELIGIBLE,
            self::DEVICE_NOT_ENROLLED,
            self::DEVICE_PENDING_APPROVAL,
            self::DEVICE_REVOKED,
            self::DEVICE_PROOF_INVALID,
            self::LINE_NOT_AUTHORIZED => 403,
            self::QR_INVALID => 404,
            self::CHALLENGE_ALREADY_USED,
            self::ATTENDANCE_ALREADY_RECORDED => 409,
            default => 422,
        };
    }

    public function mensagem(): string
    {
        return match ($this) {
            self::STUDENT_NOT_ELIGIBLE => 'Seu cadastro não está ativo para uso do transporte. Procure a responsável pelo transporte universitário.',
            self::DEVICE_NOT_ENROLLED => 'Para registrar presença, autorize este dispositivo no atendimento do transporte universitário.',
            self::DEVICE_PENDING_APPROVAL => 'A autorização do seu dispositivo ainda aguarda o atendimento.',
            self::DEVICE_REVOKED => 'Este dispositivo não está mais autorizado. Solicite uma nova autorização.',
            self::DEVICE_PROOF_INVALID => 'Não foi possível confirmar que este é o seu dispositivo autorizado.',
            self::QR_INVALID => 'QR Code não reconhecido. Leia o código afixado na van.',
            self::NO_MATCHING_TRIP => 'Não há viagem programada para esta van agora.',
            self::LINE_NOT_AUTHORIZED => 'Você não está autorizado a usar a linha desta van.',
            self::OUTSIDE_TIME_WINDOW => 'Fora do horário permitido para registrar presença nesta viagem.',
            self::LOCATION_PERMISSION_DENIED => 'Permita o acesso à localização para registrar a presença.',
            self::LOCATION_UNAVAILABLE => 'Não foi possível obter sua localização. Tente novamente.',
            self::LOCATION_INACCURATE => 'Sua localização está imprecisa. Aguarde o sinal melhorar e tente novamente.',
            self::OUTSIDE_GEOFENCE => 'Sua localização está fora da área de embarque desta viagem.',
            self::CHALLENGE_EXPIRED => 'A confirmação expirou. Leia o QR Code novamente.',
            self::CHALLENGE_ALREADY_USED => 'Esta confirmação já foi utilizada. Leia o QR Code novamente.',
            self::ATTENDANCE_ALREADY_RECORDED => 'Sua presença nesta viagem já foi registrada.',
            self::MANUAL_REVIEW_REQUIRED => 'Não foi possível validar a presença automaticamente. Você pode pedir revisão.',
        };
    }

    /**
     * Negativas que podem ter acontecido com um estudante legitimo (GPS fraco,
     * atraso da van, geocerca mal calibrada). Nelas o estudante pode pedir
     * revisao; nas de identidade ou de dispositivo, nao.
     */
    public function admiteRevisao(): bool
    {
        return in_array($this, [
            self::OUTSIDE_TIME_WINDOW,
            self::LOCATION_PERMISSION_DENIED,
            self::LOCATION_UNAVAILABLE,
            self::LOCATION_INACCURATE,
            self::OUTSIDE_GEOFENCE,
            self::MANUAL_REVIEW_REQUIRED,
        ], true);
    }
}
