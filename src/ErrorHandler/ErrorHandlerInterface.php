<?php

namespace GoldenPlanet\WebpackBundle\ErrorHandler;

use Exception;

/**
 * @api
 */
interface ErrorHandlerInterface {

	public function processException(Exception $exception);
}
