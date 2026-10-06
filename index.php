<?php

/**
 * @file index.php
 *
 * @brief Wrapper for loading the SchemaOrgPlugin class.
 */

if (!function_exists('import')) {
    return null;
}
require_once('SchemaOrgPlugin.inc.php');

return new SchemaOrgPlugin();
