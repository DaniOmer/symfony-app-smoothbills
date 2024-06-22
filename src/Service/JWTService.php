<?php

namespace App\Service;

use Lcobucci\JWT\Configuration;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class JWTService
{
    private $config;

    public function __construct(ParameterBagInterface $params)
    {
        $secretKey = $params->get('jwt_secret_key');
        $this->config = Configuration::forSymmetricSigner(
            new \Lcobucci\JWT\Signer\Hmac\Sha256(),
            \Lcobucci\JWT\Signer\Key\InMemory::plainText($secretKey)
        );
    }

    public function createToken(array $data, int $expiresIn): string
    {
        $now = new \DateTimeImmutable();
        $token = $this->config->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify("+{$expiresIn} seconds"))
            ->withClaim('data', $data)
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    public function parseToken(string $token): array
    {
        $parsedToken = $this->config->parser()->parse($token);
        $data = $parsedToken->claims()->get('data');

        return $data;
    }
}
