<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriberStatus: string
{
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';
}
