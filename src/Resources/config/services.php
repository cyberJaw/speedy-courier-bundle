<?php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Cyberjaw\SpeedyCourierBundle\Service\SpeedyClient;
use GuzzleHttp\Client;

return static function (ContainerConfigurator $c) {
    $s = $c->services()->defaults()->autowire()->autoconfigure();

    // Локален Guzzle клиент само за бандъла (не пипаме глобално алиаси)
    $s->set('speedy_courier.http', Client::class)
        ->arg('$config', [
            'base_uri'    => '%speedy_courier.base_uri%',
            'timeout'     => '%speedy_courier.timeout%',
            'http_errors' => false,
            'headers'     => ['Accept' => 'application/json'],
        ]);

    $s->set(SpeedyClient::class)
        ->arg('$http', service('speedy_courier.http'))
        ->arg('$baseUri', '%speedy_courier.base_uri%')
        ->arg('$username', '%speedy_courier.username%')
        ->arg('$password', '%speedy_courier.password%')
        ->arg('$timeout', '%speedy_courier.timeout%')
        ->arg('$sandbox', '%speedy_courier.sandbox%');
};
