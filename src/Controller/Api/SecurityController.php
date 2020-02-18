<?php

namespace App\Controller\Api;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use App\Repository\UserRepository;
use App\Entity\UserToken;
use App\Entity\User;
use App\Service\UserService;

/**
 * @Route("/security", name="api.security.")
 */
class SecurityController extends BaseApiController
{
    /**
     * @Rest\Post("/login", name="login")
     * @Rest\View(serializerGroups={"user"})
     */
    public function login(Request $request, UserService $userService)
    {
        if (empty($request->get('email')) || empty($request->get('password'))) {
            return $this->onError('Invalid credentials');
        }

        $user = $userService->findOneByEmail($request->get('email'));

        if ($user != null) {
            if (!$userService->isPasswordValid($user, $request->get('password'))) {
                return $this->onError('Invalid password');
            }

            $token = $userService->createToken($user);

            return $this->onSuccess([
              'token' => $token->getToken(),
              'user' => $user,
            ]);
        } else {
            return $this->onError('Email not found');
        }
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Post("/register", name="register")
     * @ParamConverter("tmpUser", converter="fos_rest.request_body")
     */
    public function register(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        User $tmpUser,
        UserService $userService
    )
    {
        [$user, $password] = $userService->createUser($tmpUser);
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return  $this->errorValidation($errors);
        }

        $em->persist($user);
        $em->flush();
        $token = $userService->createToken($user);

        return $this->onSuccess([
            'token' => $token->getToken(),
            'user' => $user,
        ]);
    }
}
