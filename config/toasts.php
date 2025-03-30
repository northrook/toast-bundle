<?php

// -------------------------------------------------------------------
// config\toasts
// -------------------------------------------------------------------

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Core\Symfony\{Toast, ToastService};

return static function( ContainerConfigurator $container ) : void {
    $services = $container->services()->defaults()
        ->tag( 'monolog.logger', ['channel' => 'toast'] );

    $services
            // Toast Action
        ->set( Toast::class )
        ->args( [service( ToastService::class )] )
        ->tag( 'controller.service_arguments' );

    $services
            // Toast Flashbag Handler
        ->set( ToastService::class )
        ->args( [service( 'request_stack' )] );
};
