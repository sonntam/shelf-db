<?php

function endn($array)
{
  $array_local = $array;
  return end($array_local);
}

function sortn($array)
{
  $array_local = $array;
  return sort($array_local);
}

function set(&$setvar, &$getvar) {
  $setvar = isset($getvar) ? $getvar : $setvar;
}

/**
 * Safe variable read function with default return capability.
 * Retuns $default value if $var is not set.
 * @param  mixed $var     Variable to read
 * @param  mixed $default Default value, is null by default
 * @return mixed          Returns $default if $var is not set
 */
function get(&$var, $default=null) {
  return isset($var) ? $var : $default;
}

/**
 * Generate a version string from a major, minor associative ArrayAccess
 * @param  array $version version array
 * @return string          version string
 */
function getversionstring($version) : string {
  return $version["major"].".".$version["minor"];
}

?>
