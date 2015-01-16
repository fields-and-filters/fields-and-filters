<?php

namespace Fieldsandfilters\Type;


abstract class AbstractType implements TypeInterface
{
    private $id;

    private $data;

    public function getId()
    {
        return $this->id;
    }

    public function setData($data)
    {
        return $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }


}