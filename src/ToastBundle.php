<?php

declare(strict_types=1);

namespace Core\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Symfony Toast Bundle
 *
 * @author Martin Nielsen
 */
final class ToastBundle extends AbstractBundle
{
    /**
     * @param array<array-key, mixed> $config
     * @param ContainerConfigurator   $container
     * @param ContainerBuilder        $builder
     *
     * @return void
     */
    public function loadExtension(
        array                 $config,
        ContainerConfigurator $container,
        ContainerBuilder      $builder,
    ) : void {
        $container->import( __DIR__.'/../config/toasts.php' );
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function build( ContainerBuilder $container ) : void {}
}
