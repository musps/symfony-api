<?php

namespace App\Controller\Api;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use App\Entity\UserToken;

/**
 * @Route("/private/user", name="api.private.user.")
 */
class UserController extends BaseApiController
{
    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/me", name="me")
     */
    public function me()
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->onError(self::GLOBAL_ERROR);
        }

        return $this->onSuccess([
            'user' => $user
        ]);
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Post("/update", name="update")
     */
    public function update(Request $request, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->onError(self::GLOBAL_ERROR);
        }

        $user->setFirstname($request->get('firstname') ?? $user->getFirstname());
        $user->setLastname($request->get('lastname') ?? $user->getLastname());
        $user->setEmail($request->get('email') ?? $user->getEmail());
        $user->setPhone($request->get('phone') ?? $user->getPhone());
        $user->setPassword($request->get('password') ? $encoder->encodePassword($user, $request->get('password')) : $user->getPassword());

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
          return  $this->errorValidation($errors);
        }

        $this->em->persist($user);
        $this->em->flush();
        return $this->onSuccess(['user' => $user]);
    }

    /**
     * @Rest\Get("/logout", name="logout")
     */
    public function logout(Request $request)
    {
        $bearer = $request->headers->get('authorization');
        $token = str_replace('Bearer ', '', $bearer);
        $token = $this->em->getRepository(UserToken::class)->findOneByToken($token);

        if ($token != null) {
            $this->em->remove($token);
            $this->em->flush();
            return $this->onSuccess();
        }

        return $this->onError(self::GLOBAL_ERROR);
    }
}

