<?php

namespace Fenrir\Authentication\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
class Auth
{
    public function __construct(
        private array $roles = [],
        private array $permissions = [],
        private string $redirect_url = ''
    ) {}

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }
}
