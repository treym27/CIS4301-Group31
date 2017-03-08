<?php

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;

class UserProvider implements UserProviderInterface
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->conn->fetchAssoc('select * from account where email_address = ?', array($username));

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" not found.', $username));
        }

        if ($user['TYPE'] === '1') {
            $role = array('ROLE_ADMIN');
        } else {
            $role = array('ROLE_USER');
        }

        return new User($user['EMAIL_ADDRESS'], $user['PASSWORD'], $role, true, true, true, true);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}

$app['security.firewalls'] = array(
    'admin' => array (
        'pattern' => '^/admin',
        'http' => true,
        'users' => function () use ($app) {
            return new UserProvider($app['db']);
        },
    ),
);
