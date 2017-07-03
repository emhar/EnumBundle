<?php

namespace Fervo\EnumBundle\Enum;

use Doctrine\Common\Util\Inflector;
use JMS\TranslationBundle\Model\Message;
use MyCLabs\Enum\Enum;

/**
 * {@inheritDoc}
 */
abstract class AbstractTranslatableEnum extends Enum
{
    /**
     * Returns an array of messages.
     *
     * @return array<Message>
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        foreach (self::values() as $value) {
            $messages[] = new Message($value->getTranslationKey(), 'enums');
        }
        return $messages;
    }

    public function getTranslationKey()
    {
        $enumClass = substr(static::class, strrpos(static::class, '\\') + 1);
        return Inflector::tableize($enumClass) . '.' . $this->value;
    }
}