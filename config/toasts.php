<?php

// -------------------------------------------------------------------
// config\toasts
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Symfony\Service\ToastService;
use Core\Symfony\Toast;

return static function( ContainerConfigurator $container ) : void {
    $services = $container->services()->defaults()
        ->tag( 'monolog.logger', ['channel' => 'toast'] );

    $services
            // Toast Action
        ->set( 'action.toast', Toast::class )
        ->args( [service( 'service.toast' )] )
        ->tag( 'controller.service_arguments' );

    $services
            // Toast Flashbag Handler
        ->set( 'service.toast', ToastService::class )
        ->args( [service( 'request_stack' ), service( 'logger' )] );

    // $services
    //         ->alias( Toast::class, 'action.toast' )
    //         ->alias( ToastService::class, 'service.toast' );
};
