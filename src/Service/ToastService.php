<?php

declare(strict_types=1);

namespace Core\Symfony\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Stringable, InvalidArgumentException;

final readonly class ToastService
{
    public function __construct(
        private Http\RequestStack  $requestStack,
        protected ?LoggerInterface $logger,
    ) {}

    /**
     * @param 'danger'|'info'|'notice'|'success'|'warning' $status
     * @param string                                       $message
     * @param null|string                                  $description [optional] accepts {@see Tag::INLINE}
     * @param bool                                         $compact
     * @param ?int                                         $timeout     [auto] time in seconds before the toast is dismissed
     * @param ?string                                      $icon        [auto] based on `$status`
     *
     * @return self
     */
    public function addMessage(
        string  $status,
        string  $message,
        ?string $description = null,
        bool    $compact = false,
        ?int    $timeout = null,
        ?string $icon = null,
    ) : self {
        $id = \hash( 'xxh3', $status.$message );

        $toastMessage = $this->getMessage( $id );

        if ( $toastMessage ) {
            $toastMessage->bump( $description );
        }
        else {
            $toastMessage = new ToastMessage( $id, $status, $message, $description, $compact, $timeout, $icon );
        }

        $this->getFlashBag()->set( $id, [$toastMessage] );

        return $this;
    }

    public function getMessage( string $id ) : ?ToastMessage
    {
        $message = $this->getFlashBag()->get( $id )[0] ?? null;

        return $message instanceof ToastMessage ? $message : null;
    }

    /**
     * @param bool $peek
     *
     * @return ToastMessage[]
     */
    public function getAllMessages( bool $peek = false ) : array
    {
        $flashBagMessages = $peek ? $this->getFlashBag()->peekAll() : $this->getFlashBag()->all();

        if ( ! $flashBagMessages ) {
            return [];
        }

        $messages = [];

        foreach ( $flashBagMessages as $keyOrType => $message ) {
            \assert( \is_array( $message ) && \is_string( $keyOrType ), __METHOD__ );

            if ( \strlen( $keyOrType ) === 16 && $message[0] instanceof ToastMessage ) {
                $messages[$keyOrType] = $message[0];
            }
            else {
                foreach ( $message as $title ) {
                    $title         = $this->resolveTitle( $title );
                    $status        = $this->resolveStatus( $keyOrType );
                    $id            = \hash( 'xxh3', $status.$title );
                    $messages[$id] = new ToastMessage( $id, $status, $title );
                }
            }
        }

        return $messages;
    }

    private function resolveTitle( mixed $title ) : string
    {
        if ( $title instanceof Stringable ) {
            $title = (string) $title;
        }

        if ( \is_string( $title ) ) {
            return \trim( $title );
        }

        throw new InvalidArgumentException(
            $this::class.' unsupported title type: '.\gettype( $title ),
        );
    }

    /**
     * @param string $string
     *
     * @return 'danger'|'info'|'notice'|'success'|'warning'|string
     */
    private function resolveStatus( string $string ) : string
    {
        $string = \strtolower( $string );

        if ( \in_array( $string, ['success', 'info', 'warning', 'danger', 'notice'], true ) ) {
            return $string;
        }

        $this->logger?->warning(
            'Unsupported status {status} provided. Returned {return}',
            ['status' => $string, 'return' => 'notice'],
        );

        return $string;
    }

    public function hasMessages() : bool
    {
        return ! empty( $this->getFlashBag()->peekAll() );
    }

    public function hasMessage( string $id ) : bool
    {
        return $this->getFlashBag()->has( $id );
    }

    /**
     * Retrieve the current {@see getFlashBag} from the active {@see Session}.
     *
     * @return FlashBagInterface
     */
    public function getFlashBag() : FlashBagInterface
    {
        \assert(
            $this->requestStack->getSession() instanceof FlashBagAwareSessionInterface,
            __METHOD__.' requires the Session to implement the FlashBagAwareSessionInterface.',
        );

        return $this->requestStack->getSession()->getFlashBag();
    }
}
