<?php

declare(strict_types=1);

namespace BobdenOtter\WeatherWidget;

use Bolt\Extension\BaseExtension;

class Extension extends BaseExtension
{
    public function getName(): string
    {
        return 'Dashboard Weather Widget';
    }

    public function initialize(): void
    {
        $this->addWidget(new WeatherWidget());
    }
}
