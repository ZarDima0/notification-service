<?php

declare(strict_types=1);

namespace App\Domain\Notification\Enums;

enum Priority: string
{
    case HIGH = 'high';
    case LOW = 'low';
    case MARKETING = 'marketing';
}
