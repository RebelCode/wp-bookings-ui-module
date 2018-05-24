<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Invocation\InvocableInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventInterface;
use stdClass;
use Traversable;

/**
 * Handler for UI state config. In the config we pass data needed by UI application to
 * work correctly. Such as site timezone and formats configuration.
 *
 * @since [*next-version*]
 */
class GeneralUiStateHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /**
     * Configuration of application formats to format some data.
     * For example, datetime formats.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $formatsConfig;

    /**
     * GeneralUiStateHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $formatsConfig List of available data formats in application.
     */
    public function __construct($formatsConfig)
    {
        $this->formatsConfig = $formatsConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $event EventInterface */
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $event->setParams([
            'config' => $this->_getUiConfig($this->formatsConfig),
        ]);
    }

    /**
     * Get config for UI application.
     *
     * @param array|Traversable|stdClass $formatsConfig List of available data formats in application.
     *
     * @return array UI configuration.
     */
    protected function _getUiConfig($formatsConfig)
    {
        return [
            'timezone' => $this->_getWebsiteTimezone(),
            'formats'  => $this->_prepareFormatsConfig($formatsConfig),
        ];
    }

    /**
     * Get website timezone.
     *
     * @since [*next-version*]
     *
     * @return string Timezone in `America/Indianapolis` form.
     */
    protected function _getWebsiteTimezone()
    {
        $currentOffset = get_option('gmt_offset');
        $tzstring      = get_option('timezone_string');

        // Remove old Etc mappings. Fallback to gmt_offset.
        if (false !== strpos($tzstring, 'Etc/GMT')) {
            $tzstring = '';
        }

        if (empty($tzstring)) {
            if (0 == $currentOffset) {
                $tzstring = 'UTC+0';
            } elseif ($currentOffset < 0) {
                $tzstring = 'UTC' . $currentOffset;
            } else {
                $tzstring = 'UTC+' . $currentOffset;
            }
        }

        return $tzstring;
    }

    /**
     * Prepare formats config for using in the UI.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $formatsConfig List of available data formats in application.
     *
     * @return array Prepared data formats configuration.
     */
    protected function _prepareFormatsConfig($formatsConfig)
    {
        $preparedFormatsConfig = [];
        foreach ($formatsConfig as $key => $config) {
            $preparedFormatsConfig[$key] = $this->_normalizeArray($config);
        }

        return $preparedFormatsConfig;
    }
}
