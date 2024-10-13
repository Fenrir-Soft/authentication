<?php

namespace Fenrir\Authentication\Middlewares;

use Exception;
use Fenrir\Framework\Attributes\Auth;
use Fenrir\Framework\Lib\Request;
use Fenrir\Framework\Lib\Response;
use Fenrir\Framework\Middleware;
use Fenrir\Authentication\Services\JwtService;
use Throwable;

class AuthMiddleware implements Middleware
{

    public function __construct(
        private Request $request,
        private Response $response,
        private JwtService $jwt_service
    ) {}

    /**
     * @param Closure $next
     * @return mixed
     */
    public function execute(callable $next)
    {
        /** @var Auth|null */
        $auth = $this->request->attributes->get('Auth', null);

        if (!$auth) {
            return $next();
        }


        try {
            $authorization = preg_replace('#^Bearer\s+#', '', trim($this->request->headers->get('Authorization', '')));
            if ('' === $authorization) {
                throw new Exception("Unauthenticated 1");
            }

            $decoded = $this->jwt_service->decode($authorization);

            if (!empty($auth->getPermissions())) {
                if (!isset($decoded->acl)) {
                    throw new Exception("Forbiden", 403);
                }

                foreach ($auth->getPermissions() as $permission_required) {
                    if (!in_array($permission_required, $decoded->acl)) {
                        throw new Exception("Forbiden: {$permission_required} required", 403);
                    }
                }
            }

            if (!empty($auth->getRoles())) {
                if (!isset($decoded->role)) {
                    throw new Exception("Forbiden", 403);
                }

                if (!in_array($decoded->role, $auth->getRoles())) {
                    throw new Exception("Forbiden", 403);
                }
            }

            $this->request->attributes->set('authenticated_user', $decoded);

            $next();
        } catch (Throwable $th) {
            if ($auth->getRedirectUrl() !== '') {
                $this->response->setStatusCode(307);
                $this->response->headers->set('Location', $auth->getRedirectUrl());
                return;
            }

            $code = 401;
            if ($th->getCode() === 403) {
                $code = 403;
            }
            $this->response->setStatusCode($code, $th->getMessage());
        }
    }
}
