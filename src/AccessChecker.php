<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 06.03.18
 */

namespace GepurIt\AccessManagerBundle;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class Guard
 * @package AppBundle\Security
 */
class AccessChecker
{
    /**
     * @var TokenStorage
     */
    private $token;

    /**
     * @var AccessDecisionManager
     */
    private $manager;

    /**
     * ReportTypeGuard constructor.
     * @param TokenStorage $token
     * @param AccessDecisionManager $manager
     */
    public function __construct(TokenStorage $token, AccessDecisionManager $manager)
    {
        $this->token = $token;
        $this->manager = $manager;
    }

    /**
     * @param string $resource
     * @return bool
     */
    public function accepted(string $resource) :bool
    {
        /** @var UserInterface $user */
        $user = $this->token->getToken()->getUser();

        if (null === $user) {
            return false;
        }

        if (!$this->manager->isGranted($user, 'READ', $resource)) {
            return false;
        }

        return true;
    }
}

