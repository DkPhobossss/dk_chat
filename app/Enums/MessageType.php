<?php
namespace App\Enums;

enum MessageType: string
{
    case MessageSent = 'message_sent';
    case MessageUpdated = 'message_updated';
    case MessageRestored = 'message_restored';
    case MessageDestroyed = 'message_destroyed';
}