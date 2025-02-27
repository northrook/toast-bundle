<?php

declare(strict_types=1);

namespace Core\Symfony;

use Core\Interface\ActionInterface;
use Core\Symfony\Service\ToastService;
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
        ?int    $timeout = null,
        ?string $icon = null,
    ) : void {
        $this->getService()->addMessage( $status, $message, $description, $timeout, $icon );
    }

    public function getService() : ToastService
    {
        return $this->toast;
    }
}
