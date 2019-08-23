<?php

declare(strict_types=1);

namespace BobdenOtter\WeatherWidget;

use Bolt\Widget\BaseWidget;
use Bolt\Widget\CacheAware;
use Bolt\Widget\CacheTrait;
use Bolt\Widget\Injector\AdditionalTarget;
use Bolt\Widget\Injector\RequestZone;
use Bolt\Widget\StopwatchAware;
use Bolt\Widget\StopwatchTrait;
use Bolt\Widget\TwigAware;
use Symfony\Component\HttpClient\HttpClient;

class WeatherWidget extends BaseWidget implements TwigAware, CacheAware, StopwatchAware
{
    use CacheTrait;
    use StopwatchTrait;

    protected $name = 'Weather Widget';
    protected $target = AdditionalTarget::WIDGET_BACK_DASHBOARD_ASIDE_TOP;
    protected $priority = 200;
    protected $template = '@weather-widget/weather.html.twig';
    protected $zone = RequestZone::BACKEND;
    protected $cacheDuration = 1800;

    protected $location = '';
    public function run(array $params = []): ?string
    {
        $weather = $this->getWeather();

        if (empty($weather)) {
            return null;
        }

        return parent::run(['weather' => $weather]);
    }

    private function getWeather(): array
    {
        $url = 'http://wttr.in/' . $this->getLocation() .  '?format=%c|%C|%h|%t|%w|%l|%m|%M|%p|%P';

        $details = [];

        try {
            $client = HttpClient::create();
            $result = $client->request('GET', $url, ['timeout' => 2.5])->getContent();
            if (mb_substr_count($result, '|') === 9) {
                $details = explode('|', trim($result));
            }
        } catch (\Exception $e) {
            // Do nothing, fall through to empty array
        }

        return $details;
    }

    private function getLocation(): string
    {
        if (! $this->extension) {
            return '';
        }

        return (string) $this->extension->getConfig()->get('location');
    }
}
