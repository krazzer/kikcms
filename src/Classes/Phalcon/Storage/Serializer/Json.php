<?php

namespace KikCMS\Classes\Phalcon\Storage\Serializer;


use InvalidArgumentException;
use JsonSerializable;
use Phalcon\Storage\Serializer\Json as JsonSerializer;

class Json extends JsonSerializer
{
    /**
     * @inheritDoc
     */
    public function serialize(): string
    {
        if (is_object($this->data) && ! ($this->data instanceof JsonSerializable)) {
            throw new InvalidArgumentException("Data cannot be of type 'object' without 'JsonSerializable'");
        }

        if ( ! $this->isSerializable($this->data)) {
            return $this->data ?: '';
        }

        return json_encode($this->data, JSON_THROW_ON_ERROR) ?: '';
    }

    /**
     * @inheritDoc
     */
    public function unserialize($data): void
    {
        if( ! $data){
            $this->data = null;
            return;
        }

        $this->data = json_decode($data, null, 512, JSON_THROW_ON_ERROR);
    }
}