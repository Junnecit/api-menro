<?php

namespace App\Mail;

use Illuminate\Notifications\Messages\MailMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

class EmbedsMenroLogo
{
    /**
     * Embed the MENRO logo as a CID attachment and point the header's
     * MENRO_LOGO_CID placeholder (see resources/views/vendor/mail/html/message.blade.php)
     * at it, since the mail theme is shared by every notification.
     */
    public static function embed(MailMessage $message): MailMessage
    {
        return $message->withSymfonyMessage(function (Email $symfonyMessage) {
            $part = (new DataPart(new File(public_path('images/menro-logo.png')), 'menro-logo.png'))->asInline();
            $symfonyMessage->addPart($part);

            $cid = 'cid:'.$part->getContentId();

            $symfonyMessage->html(str_replace('MENRO_LOGO_CID', $cid, (string) $symfonyMessage->getHtmlBody()));
        });
    }
}
