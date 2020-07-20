<?php

namespace Armor\Handle;

/**
 * This class allow its own instances and/or its subclasses
 * instances to receive additional, external-defined, fields
 * or functions.
 * 
 * This class is the super-class of both the `Request` and
 * `Response` classes.
 */
class ExtensibleObject {
  /**
   * The place where the additional fields and/or functions
   * are stored.
   * 
   * @var array
   */
  private $extensions;

  public function __construct() {
    $this->extensions = array();
  }

  /** @ignore */
  public function __get($attributeName) {
    if (array_key_exists($attributeName, $this->extensions)) {
      return $this->extensions[$attributeName];
    }

    return null;
  }

  /** @ignore */
  public function __set($attributeName, $attributeValue) {
    $this->extensions[$attributeName] = $attributeValue;
  }

  /** @ignore */
  public function __call($methodName, $methodArguments) {
    $methodObject = $this->__get($methodName);

    if (!is_callable($methodObject)) {
      throw new \Error("'$methodName' is not a method, or does not exist");
    }

    return call_user_func_array($methodObject, $methodArguments);
  }
}