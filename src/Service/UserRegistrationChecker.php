<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\UserRepository;

class UserRegistrationChecker
{
    private $cache;
    private $userRepository;
    private $security;

    public function __construct(AdapterInterface $cache, UserRepository $userRepository, Security $security)
    {
        $this->cache = $cache;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    public function isRegistrationComplete(): bool
    {
        $user = $this->security->getUser();
        if (!$user) {
            return false;
        }

        $cacheKey = 'user_registration_' . $user->getId();
        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            $isComplete = $this->checkRegistrationInDatabase($user->getId());
            $cacheItem->set($isComplete);
            $this->cache->save($cacheItem);
        } else {
            $isComplete = $cacheItem->get();
        }

        return $isComplete;
    }

    private function checkRegistrationInDatabase(int $userId): bool
    {
        $user = $this->userRepository->find($userId);
        return $user && $user->isRegistrationComplete();
    }

    public function clearRegistrationCache(int $userId): void
    {
        $cacheKey = 'user_registration_' . $userId;
        $this->cache->deleteItem($cacheKey);
    }

    public function updateRegistrationCache(int $userId): void
    {
        $cacheKey = 'user_registration_' . $userId;
        $this->cache->deleteItem($cacheKey);

        $isComplete = $this->checkRegistrationInDatabase($userId);

        $cacheItem = $this->cache->getItem($cacheKey);
        
        $cacheItem->set($isComplete);
        $this->cache->save($cacheItem);
    }
}
