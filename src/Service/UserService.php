<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Entity\UserToken;

class UserService
{
    private $em;
    private $cr;
    private $encoder;
    private $rep;

    public function __construct(
      EntityManagerInterface $em,
      UserRepository $cr,
      UserPasswordEncoderInterface $encoder,
      UserRepository $rep
    )
    {
        $this->em = $em;
        $this->cr = $cr;
        $this->encoder = $encoder;
        $this->rep = $rep;
    }

    public function createToken(User $user): UserToken
    {
        $token = new UserToken();
        $token->setUser($user);
        $token->setToken($this->generateToken());
        $this->em->persist($token);
        $this->em->flush();
        return $token;
    }

    public function createUser(User $tmpUser, bool $children = false): array
    {
        $user = new User();
        $user->setRoles([User::ROLE_USER]);
        $user->setFirstname($tmpUser->getFirstname());
        $user->setLastname($tmpUser->getLastname());
        $user->setEmail($tmpUser->getEmail());
        $user->setPhone($tmpUser->getPhone());
        $user->setPassword($this->generateHashPassword($user, $tmpUser->getPassword()));

        return [
            $user,
            $tmpUser->getPassword(),
        ];
    }

    public function updatePassword(User $user, string $password)
    {
        $hash = $this->generateHashPassword($user, $password);
        $user->setPassword($hash);
        return $user;
    }

    public function generateHashPassword(User $user, $password)
    {
        return $this->encoder->encodePassword($user, $password);
    }

    public function isPasswordValid(User $user, string $password)
    {
        return $this->encoder->isPasswordValid($user, $password);
    }

    public function generateToken()
    {
        return bin2hex(random_bytes(64));
    }

    public function findOneByEmail(string $email)
    {
        return $this->cr->findOneByEmail($email);
    }

    public function resetPasswordGenerateCode(User $user): User
    {
        $expireAt = new \DateTime('now');
        $expireAt->add(new \DateInterval('PT2H'));

        $user->setResetPasswordCode(random_int(10000, 99999));
        $user->setResetPasswordExpireAt($expireAt);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function resetPasswordCheckCode(string $email, string $code)
    {
        $user = $this->cr->findOneBy([
            'email' => $email,
            'resetPasswordCode' => $code,
        ]);

        if (empty($user)) {
            return false;
        }

        $next = $user->getResetPasswordExpireAt()->getTimestamp();
        $current = (new \DateTime('now'))->getTimestamp();
        return $next >= $current;
    }

    public function resetPasswordByCode(string $email, string $code, string $password)
    {
        $user = $this->cr->findOneBy([
            'email' => $email,
            'resetPasswordCode' => $code,
        ]);

        if (empty($user)) {
            return false;
        }

        $user->setPassword($this->generateHashPassword($user, $password));
        $user->setResetPasswordCode(null);
        $user->setResetPasswordExpireAt(null);
        $this->em->persist($user);
        $this->em->flush();
        return true;
    }


    public function generateTempPassword(User $user)
    {
        $code = $user->getFirstname()[0] . $user->getLastname()[0];
        $code .= random_int(1000, 10000);
        $code = strtoupper($code);
        return $code;
    }
}
