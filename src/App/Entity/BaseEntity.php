<?php

namespace App\Entity;

use App\Repository\AlmacenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


class BaseEntity
{
    public function toJsonAttributes($attributes){
        $result = [];
        foreach ($attributes as $attribute){
            $prefixes = ['get', 'is'];
            foreach ($prefixes as $prefix) {
                $attribute_get = $prefix . ucfirst($attribute);
                if(method_exists($this, $attribute_get)){
                    if (gettype($this->$attribute_get()) === 'object') {

                        if (get_class($this->$attribute_get()) === 'DateTime') {
                            $result[$attribute] = $this->$attribute_get()->format('d-m-Y h:i:s a');
                        } elseif (strpos(get_class($this->$attribute_get()), 'App\Entity')) {
                            $result[$attribute] = $this->$attribute_get()->getId();
                        } else {
                            $result[$attribute_get] = null;
                        }
                    } else {
                        $result[$attribute] = $this->$attribute_get();
                    }
                }
            }

        }
        return json_encode($result);
    }
}
