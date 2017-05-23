<?php
namespace NYPL\Starter;

use Slim\Http\Request;
use Slim\Http\Response;
use NYPL\Starter\Model\Response\ErrorResponse;
use Slim\Container;

class DefaultContainer extends Container
{
    const DEFAULT_ERROR_STATUS_CODE = 500;

    /**
     * @param \Throwable $exception
     *
     * @return int
     */
    protected function getStatusCode(\Throwable $exception)
    {
        if ($exception instanceof APIException) {
            return $exception->getHttpCode();
        }

        return self::DEFAULT_ERROR_STATUS_CODE;
    }

    protected function initializeErrorResponse(\Throwable $exception, ErrorResponse $errorResponse)
    {
        $errorResponse->setStatusCode($this->getStatusCode($exception));
        $errorResponse->setType('exception');
        $errorResponse->setMessage($exception->getMessage());
        $errorResponse->setError($errorResponse->translateException($exception));
    }

    /**
     * @param \Exception|\Throwable $exception
     *
     * @return ErrorResponse
     */
    protected function getErrorResponse(\Throwable $exception)
    {
        if ($exception instanceof APIException && $exception->getErrorResponse()) {
            $errorResponse = $exception->getErrorResponse();
        } else {
            $errorResponse = new ErrorResponse();
        }

        $this->initializeErrorResponse($exception, $errorResponse);

        return $errorResponse;
    }

    public function __construct()
    {
        parent::__construct();

        $this["settings"]["displayErrorDetails"] = false;

        $this["notFoundHandler"] = function (Container $container) {
            return function (Request $request, Response $response) use ($container) {
                return $container["response"]
                    ->withStatus(404)
                    ->withHeader("Content-Type", "text/html")
                    ->write("Page not found");
            };
        };
    }
}
