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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
        $url = 'wttr.in/' . $this->location .  '?format=%c|%C|%h|%t|%w|%l|%m|%M|%p|%P';

        $details = [];

        try {
            $client = new Client();
            $result = $client->request('GET', $url)->getBody()->getContents();
            if (mb_substr_count($result, '|') === 9) {
                $details = explode('|', trim($result));
            }
        } catch (RequestException $e) {
            // Do nothing, fall through to empty array
        }

        return $details;
    }
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }
}
