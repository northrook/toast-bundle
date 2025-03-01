<?php

declare(strict_types=1);

namespace Core\Symfony;

use Core\Interface\ActionInterface;
use JetBrains\PhpStorm\ExpectedValues;

final class Toast implements ActionInterface
{
    public const string
        INFO    = 'info',
        NOTICE  = 'notice',
        SUCCESS = 'success',
        WARNING = 'warning',
        ERROR   = 'danger';

    public function __construct( private readonly ToastService $toast ) {}

    /**
     * @param self::* $status
     * @param string  $message
     * @param ?string $description [optional]
     * @param bool    $compact
     * @param ?int    $timeout     [auto] time in seconds before the toast is dismissed
     * @param ?string $icon        [auto] based on `$status`
     *
     * @return void
     */
    public function __invoke(
        #[ExpectedValues( valuesFromClass : Toast::class )]
        string  $status,
        string  $message,
        ?string $description = null,
        bool    $compact = false,
        ?int    $timeout = null,
        ?string $icon = null,
    ) : void {
        $this->getService()->addMessage( $status, $message, $description, $compact, $timeout, $icon );
    }

    public function info(
        string  $message,
        ?string $description = null,
        bool    $compact = false,
        ?int    $timeout = null,
    ) : void {
        $this->__invoke( $this::INFO, $message, $description, $compact, $timeout );
    }

    public function notice(
        string  $message,
        ?string $description = null,
        bool    $compact = false,
        ?int    $timeout = null,
    ) : void {
        $this->__invoke( $this::NOTICE, $message, $description, $compact, $timeout );
    }

    public function success(
        string  $message,
        ?string $description = null,
        bool    $compact = false,
        ?int    $timeout = null,
    ) : void {
        $this->__invoke( $this::SUCCESS, $message, $description, $compact, $timeout );
    }

    public function warning(
        string  $message,
        ?string $description = null,
        bool    $compact = false,
        ?int    $timeout = null,
    ) : void {
        $this->__invoke( $this::WARNING, $message, $description, $compact, $timeout );
    }

    public function danger(
        string  $message,
        ?string $description = null,
        bool    $compact = false,
    ) : void {
        $this->__invoke( $this::ERROR, $message, $description, $compact );
    }

    /**
     * @internal
     *
     * @return ToastService
     */
    public function getService() : ToastService
    {
        return $this->toast;
    }
}
