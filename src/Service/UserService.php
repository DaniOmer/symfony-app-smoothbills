<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;

class UserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
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
}