<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/*
 * Enrobage léger autour de PHPMailer pour l'envoi d'e-mails transactionnels
 * via SMTP. Tout est « fail-soft » : si la configuration est absente (host
 * vide), l'envoi est silencieusement désactivé et envoyer() renvoie false.
 */
class Mailer
{
    /** @var array<string,mixed> */
    private static array $config = [];

    /** Mémorise la configuration SMTP (appelé au bootstrap). */
    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    /** Vrai si un serveur SMTP est configuré (sinon l'envoi est désactivé). */
    public static function estActif(): bool
    {
        return trim((string) (self::$config['host'] ?? '')) !== '';
    }

    /**
     * Envoie un e-mail HTML. Renvoie true en cas de succès, false si l'envoi
     * est désactivé ou a échoué (l'erreur est journalisée, jamais propagée).
     */
    public static function envoyer(
        string $destinataire,
        string $sujet,
        string $corpsHtml,
        string $corpsTexte = ''
    ): bool {
        if (!self::estActif()) {
            return false;
        }

        $c = self::$config;
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = (string) $c['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) ($c['user'] ?? '');
            $mail->Password   = (string) ($c['pass'] ?? '');
            $mail->Port       = (int) ($c['port'] ?? 465);
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;

            $secure = strtolower((string) ($c['secure'] ?? 'ssl'));
            $mail->SMTPSecure = $secure === 'tls'
                ? PHPMailer::ENCRYPTION_STARTTLS
                : PHPMailer::ENCRYPTION_SMTPS;

            $mail->setFrom(
                (string) ($c['from_email'] ?? $c['user'] ?? ''),
                (string) ($c['from_name'] ?? '')
            );
            $mail->addAddress($destinataire);

            $mail->isHTML(true);
            $mail->Subject = $sujet;
            $mail->Body    = $corpsHtml;
            $mail->AltBody = $corpsTexte !== ''
                ? $corpsTexte
                : trim(strip_tags($corpsHtml));

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Mailer: échec d\'envoi à ' . $destinataire . ' : ' . $mail->ErrorInfo);
            return false;
        } catch (\Throwable $e) {
            error_log('Mailer: erreur inattendue : ' . $e->getMessage());
            return false;
        }
    }
}
