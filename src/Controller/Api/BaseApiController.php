<?php

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class BaseApiController extends AbstractFOSRestController
{
    const GLOBAL_ERROR = 'Something went wrong.';

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $data
     * @param int $httpErrorCode
     * @return View
     */
    public function onSuccess($data = null, string $message = null, int $httpErrorCode = Response::HTTP_OK)
    {
        $res = [
          'data' => $data,
          'message' => $message,
        ];
        return $this->view($res, $httpErrorCode);
    }

    /**
     * @param $message
     * @param int $httpErrorCode
     * @return View
     */
    public function onSuccessMessage(string $message = null, int $httpErrorCode = Response::HTTP_OK): View
    {
        return $this->onSuccess([], $message, $httpErrorCode);
    }

    /**
     * @return View
     */
    public function errorValidation(ConstraintViolationList $violation, string $message = 'Une erreur s\'est produite'): View
    {
        $errors = [];
        foreach ($violation as $error) {
            $errors[$error->getPropertyPath()] = $error->getMessage();
        }
        return $this->onError($message, [
          'errors' => $errors,
        ]);
    }

    /**
     * @return View
     */
    public function errorsValidation(?string $message = 'Une erreur s\'est produite', ConstraintViolationList ...$violations): View
    {
        $errors = [];

        foreach ($violations as $violation) {
            foreach ($violation as $error) {
                $errors[$error->getPropertyPath()] = $error->getMessage();
            }
        }

        return $this->onError($message, [
          'errors' => $errors,
        ]);
    }

    /**
     * @param $errors
     * @param int $httpErrorCode
     * @return View
     */
    public function onError(string $message = null, $errors = [], $httpErrorCode = Response::HTTP_BAD_REQUEST)
    {
        return $this->onSuccess($errors, $message, $httpErrorCode);
    }
}
