<?php 
namespace phpsec\framework;
/**
 * Specify routes here.
 * The keys are URLs, the values are the controller that will be called
 * Priority is based on the array, so keep the wildcard default on last line.
 * Wildcards can be used to point to DefaultControllers
 * @note: wildcards only supported at the rightmost character
 */

FrontController::$Routes["hello/*"]="default"; //route everything else to default
FrontController::$Routes["*"]="default"; //route everything else to default
