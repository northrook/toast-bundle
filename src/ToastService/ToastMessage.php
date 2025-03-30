<?php

declare(strict_types=1);

namespace Core\Symfony\ToastService;

use Core\Interface\{DataInterface, IconProviderInterface, ViewInterface};
use Stringable;
use InvalidArgumentException;
use function Support\{escape_html, datetime};
use const Support\TAG_INLINE;

/**
 * @internal
 */
final class ToastMessage implements DataInterface, ViewInterface
{
    private ?ToastView $view = null;

    /** @var array<int, ?string> `[timestamp => ?description]` */
    private array $occurrences = [];

    /** @var ?int in seconds */
    private ?int $timeout;

    public readonly string $status;

    public readonly string $message;

    public readonly bool $compact;

    public readonly ?string $icon;

    /**
     * @param string  $id          a 16 character hash
     * @param string  $status
     * @param string  $message
     * @param ?string $description [optional] accepts {@see \Support\TAG_INLINE}
     * @param bool    $compact
     * @param ?int    $timeout     [auto] time in seconds before the toast is dismissed
     * @param ?string $icon        [auto] based on `$status`
     */
    public function __construct(
        public readonly string $id,
        string                 $status,
        string                 $message,
        ?string                $description = null,
        bool                   $compact = false,
        ?int                   $timeout = null,
        ?string                $icon = null,
    ) {
        $this->setStatus( $status );
        $this->message = escape_html( $message );
        $this->compact = $compact;
        $this->bump( $description );
        $this->timeout( $timeout );
        $this->setIcon( $icon );
    }

    /**
     * Indicate that this notification has been seen before.
     *
     * - Adds a timestamp to the {@see ToastMessage::$occurrences} array.
     * - May update the `$description`.
     *
     * @param null|string|Stringable $description
     *
     * @return $this
     */
    public function bump( null|string|Stringable $description ) : self
    {
        $details = (string) $description;
        $details = $details ? \trim( \strip_tags( $details, TAG_INLINE ) ) : null;

        $this->occurrences[datetime()->getTimestamp()] = $details;
        return $this;
    }

    public function timeout( ?int $set = null ) : self
    {
        $this->timeout = $set;

        return $this;
    }

    /**
     * @return array<int, ?string> `[timestamp => ?description]`
     */
    public function getOccurrences() : array
    {
        return $this->occurrences;
    }

    public function getTimeout() : ?int
    {
        return $this->status === 'danger' ? null : $this->timeout;
    }

    public function getDescription() : ?string
    {
        return $this->getOccurrences()[\array_key_last( $this->occurrences )] ?? null;
    }

    public function getTimestamp() : int
    {
        return (int) ( \array_key_last( $this->getOccurrences() ) ?? \time() );
    }

    public function getView( ?IconProviderInterface $iconProvider = null ) : ToastView
    {
        $this->view ??= new ToastView( $this, $iconProvider );

        $this->view->compact( $this->compact );

        return $this->view;
    }

    public function __toString() : string
    {
        return $this->getView()->__toString();
    }

    public function getHtml() : Stringable
    {
        return $this->getView()->getHtml();
    }

    protected function setStatus( string $status ) : void
    {
        if ( ! \ctype_alpha( $status ) ) {
            $message = $this::class.' invalid status type; may only contain ASCII letters.';
            throw new InvalidArgumentException( $message );
        }

        $this->status = \strtolower( $status );
    }

    protected function setIcon( ?string $icon ) : void
    {
        if ( $icon && ! \ctype_alpha( \str_replace( [':', '.', '-'], '', $icon ) ) ) {
            $message
                    = $this::class.' invalid icon key; may only contain ASCII letters and colon, period, or hyphens.';
            throw new InvalidArgumentException( $message );
        }

        $this->icon = $icon ? \strtolower( $icon ) : null;
    }
}
