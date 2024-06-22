<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class UserService
{
    private $userRepository;
    private $entityManager;
    private $mailer;
    private $params;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, MailerInterface $mailer, ParameterBagInterface $params,)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->params = $params;
    }
    
    public function getPaginatedUsers(User $user, $page): PaginationInterface
    {
        $paginateUsers = $this->userRepository->paginateUsersByOwner($user, $page);

        return $paginateUsers;
    }

    public function getUsersRows(User $user, $page): Array
    {
        $rows = [];

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            $userRole = 'Admin';
        } elseif (in_array('ROLE_EDITOR', $roles, true)) {
            $userRole = 'Editeur';
        } elseif (in_array('ROLE_ACCOUNTANT', $roles, true)) {
            $userRole = 'Comptable';
        }

        foreach ($this->getPaginatedUsers($user, $page) as $user) {
            $rows[] = [
                'name' => $user->getFirstName()." ".$user->getLastName(),
                'mail' => $user->getEmail(),
                'role' => $userRole,
                'jobTitle' => $user->getJobTitle(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d'),
                'uid' => $user->getUid(),
                'id' => $user->getId(),
            ];
        }

        return $rows;
    }

    public function createInvitation($user, $company, $invitation, $inviteUrl, $email, $token): void
    {
        $invitation->setToken($token);
        $invitation->setExpireAt(new \DateTimeImmutable('+1 day'));
        $invitation->setCompany($company);
        $invitation->setOwner($user);

        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        $this->sendInvitationMail($user, $email, $inviteUrl);
    }


    public function sendInvitationMail($user, $email, $inviteUrl)
    {
        $subject = $user->getEmail().' vous invite à collaborer sur Smoothbill';

        $emailMessage = (new TemplatedEmail())
            ->from(new Address($this->params->get('admin_email'), 'Smoothbill'))
            ->to($email)
            ->subject($subject)
            ->htmlTemplate('/dashboard/user/mail/invitation_email.html.twig')
            ->context([
                'user' => $user,
                'inivteUrl' => $inviteUrl
            ]);

        $this->mailer->send($emailMessage);
    }
}