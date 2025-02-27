<?php

declare(strict_types=1);

namespace Core\Symfony\Service;

use Core\Interface\DataInterface;
use Throwable, InvalidArgumentException;
use function Support\{escape_html, timestamp};

/**
 * @internal
 */
final class ToastMessage implements DataInterface
{
    /** @var array<int, ?string> `[timestamp => ?description]` */
    private array $occurrences = [];

    /** @var ?int in seconds */
    private ?int $timeout;

    public readonly string $status;

    public readonly string $message;

    public readonly ?string $icon;

    /**
     * @param string  $id          a 16 character hash
     * @param string  $status
     * @param string  $message
     * @param ?string $description [optional] accepts {@see Tag::INLINE}
     * @param ?int    $timeout     [auto] time in seconds before the toast is dismissed
     * @param ?string $icon        [auto] based on `$status`
     */
    public function __construct(
        public readonly string $id,
        string                 $status,
        string                 $message,
        ?string                $description = null,
        ?int                   $timeout = null,
        ?string                $icon = null,
    ) {
        $this->setStatus( $status );
        $this->message = escape_html( $message );
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
     * @param ?string $description
     *
     * @return $this
     */
    public function bump( ?string $description ) : self
    {
        $this->occurrences[timestamp()->getTimestamp()] = $this->sanitizeHtml( $description );
        return $this;
    }

    public function timeout( ?int $set = null ) : self
    {
        $this->timeout = $set;

        return $this;
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

    public function getTimeout() : ?int
    {
        return $this->status === 'danger' ? null : $this->timeout;
    }

    /**
     * @return array{id: string, status: string, message: string, description: null|string, timeout: null|int, instances: array<int, ?string>, timestamp: null|int, icon: null|string, attributes: array<string, string>}
     */
    public function getArguments() : array
    {
        /** @var ?string $description */
        $description = \array_reverse( \array_filter( $this->occurrences ) )[0] ?? null;

        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'message'     => $this->message,
            'description' => $description,
            'timeout'     => $this->getTimeout(),
            'instances'   => $this->occurrences,
            'timestamp'   => (int) \array_key_first( $this->occurrences ),
            'icon'        => $this->icon,
            'attributes'  => ['class' => 'hidden'],
        ];
    }

    /**
     * @param null|string|string[] $string
     *
     * @return null|string
     */
    private function sanitizeHtml( null|string|array $string ) : ?string
    {
        if ( \is_array( $string ) ) {
            try {
                $string = \implode( PHP_EOL, $string );
            }
            catch ( Throwable $exception ) {
                throw new InvalidArgumentException( $exception->getMessage() );
            }
        }

        if ( ! $string ) {
            return null;
        }

        $preserve = [
            'a',
            'b',
            'strong',
            'cite',
            'code',
            'em',
            'i',
            'kbd',
            'mark',
            'span',
            's',
            'small',
            'wbr',
        ];

        $string = \strip_tags( $string, $preserve );

        return \trim( $string );
    }
}
