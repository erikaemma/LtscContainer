<?php

namespace LTSC;


use LTSC\Helper\StructContainerClass;
use LTSC\Helper\StructContainerSingle;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $classMap = [];

    protected $singleMap = [];

    protected $alias = [];

    public function set($id, $injection, int $argNums = 0, bool $cover = true, string $getInstance = 'getInstance'): bool {
        if(key_exists($id, $this->alias)) {
            if(!$cover)
                return false;
            return $this->_add($id, $injection, $argNums, $getInstance)[0];
        }
        $result = $this->_add($id, $injection, $argNums, $getInstance);
        if($result[0]) {
            $this->alias[$id] = $result[1];
            return true;
        } else {
            return false;
        }
    }

    public function get($id, ...$args) {
        if(key_exists($id, $this->alias)) {
            if($this->alias[$id]) {
                $struct = $this->singleMap[$id];
                $class = new \ReflectionClass($struct->injection);
                $method = $class->getMethod($struct->getInstance);
                if($struct->argNums == 0) {
                    return $method->invoke(null);
                } else {
                    if(count($args) != $struct->argNums)
                        return null;
                    return $method->invokeArgs(null, $args);
                }
            } else {
                $struct = $this->classMap[$id];
                $injection = $struct->injection;
                $type = $struct->type;
                if($type == StructContainerClass::SCS_TYPE_STRING) {
                    $class = new \ReflectionClass($injection);
                    if($struct->argNums == 0) {
                        return $class->newInstance();
                    } else {
                        if(count($args) != $struct->argNums)
                            return null;
                        return $class->newInstanceArgs($args);
                    }
                } elseif($type == StructContainerClass::SCS_TYPE_OBJECT) {
                    return $injection;
                } elseif($type == StructContainerClass::SCS_TYPE_CALLBACK) {
                    return $injection();
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }
    }

    public function has($id) {
        return key_exists($id, $this->alias);
    }

    public function _add($id, $injection, $argNums, $getInstance): array {
        if($this->_isSingle($injection)) {
            $this->singleMap[$id] = new StructContainerSingle($injection, $getInstance, $argNums);
            return [true, true];
        } else {
            $type = null;
            if(is_string($injection)) {
                if(!class_exists($injection))
                    return [false];
                $type = StructContainerClass::SCS_TYPE_STRING;
            } elseif(is_callable($injection)) {
                $type = StructContainerClass::SCS_TYPE_CALLBACK;
            } elseif(is_object($injection)) {
                $type = StructContainerClass::SCS_TYPE_OBJECT;
            } else {
                return [false];
            }
            $struct = new StructContainerClass($injection, $argNums, $type);
            $this->classMap[$id] = $struct;
            return [true, false];
        }
    }

    public function _isSingle($injection, string $getInstance = 'getInstance'): bool {
        $class = null;
        if(is_string($injection)) {
            if(!class_exists($injection))
                return false;
            $class = new \ReflectionClass($injection);
        } else {
            return false;
        }
        if(!$class->hasMethod($getInstance))
            return false;
        $method = $class->getMethod($getInstance);
        if($method->isStatic()) {
            if($class->getConstructor()->isProtected() || $class->getConstructor()->isPrivate())
                return true;
        }
        return false;
    }
}