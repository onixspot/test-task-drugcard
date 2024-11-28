<?php

namespace App\MessageHandler;

use App\Message\GrabResourceMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GrabResourceMessageHandler
{
    public function __invoke(GrabResourceMessage $message): void
    {
        // do something with your message
    }
}
