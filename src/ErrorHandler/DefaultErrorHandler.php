<?php

namespace GoldenPlanet\WebpackBundle\ErrorHandler;

use Exception;

class DefaultErrorHandler implements ErrorHandlerInterface
{
	public function processException(Exception $exception)
	{
		throw $exception;
	}
}
