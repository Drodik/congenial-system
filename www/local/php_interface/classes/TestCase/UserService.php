<?php

namespace TestCase;

use Bitrix\Main;
use \Bitrix\Main\Context;

class UserService extends \CBitrixComponent
{
    const LOGIN = 'login';
    const PASSWD = '111';
    private $connection;
    private TokenService $tokenService;
    protected $serviceRequest;

    public function __construct()
    {
        $this->connection = Main\Application::getConnection();
        $this->tokenService = new TokenService;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function createUser(): array
    {
        $result = [];

        $token = $this->getRequestToken($this->serviceRequest['headers']['authorization']);
        $tokenInfo = $this->getTokenInfo(['TOKEN' => $token]);
        $tokenId = $tokenInfo[$token]['id'];

        if (!$tokenId) {
            throw new \Exception('Token not valid');
        }
        if (empty($this->serviceRequest['values']['email'])) {
            throw new \Exception('The required email field was not passed');
        }

        $this->connection->startTransaction();

        $email = $this->serviceRequest['values']['email'];
        $password = \randString(10);

        $fields = [
            'NAME' => (!empty($this->serviceRequest['values']['name'])) ? $this->serviceRequest['values']['name'] : null,
            'LAST_NAME' => (!empty($this->serviceRequest['values']['last_name'])) ? $this->serviceRequest['values']['last_name'] : null,
            'EMAIL' => $email,
            'PERSONAL_PHONE' => (!empty($this->serviceRequest['values']['phone'])) ? $this->serviceRequest['values']['phone'] : null,
            'LOGIN' => $email,
            'PASSWORD' => $password
        ];

        $user = new \CUser;

        $userId = $user->add($fields);

        if ($userId) {

            $result = [
                'id' => $userId,
                'login' => $email,
                'password' => $password
            ];

        } else {
            $this->connection->rollbackTransaction();
            throw new \Exception($user->LAST_ERROR);
        }

        if (!$this->tokenService->update($tokenId, ['USER_ID' => $userId])) {
            $this->connection->rollbackTransaction();
            throw new \Exception(implode("\n", $this->tokenService->getErrors()));
        }

        $this->connection->commitTransaction();

        return $result;
    }

    public function removeUserById(): bool
    {
        $result = false;


        $id = $this->serviceRequest['values']['id'];

        if (empty($id)) {
            throw new \Exception('The required id field was not passed');
        }

        $token = $this->getRequestToken($this->serviceRequest['headers']['authorization']);
        $tokenInfo = $this->getTokenInfo(['TOKEN' => $token]);
        $tokenId = $tokenInfo[$token]['id'];

        if (!$tokenId) {
            throw new \Exception('Token not valid');
        }

        $this->connection->startTransaction();

        $user = new \CUser;

        if ($user->delete($id)) {

            $tokenInfo = $this->getTokenInfo(['USER_ID' => $id]);

            foreach ($tokenInfo as $elem) {

                if (!$this->tokenService->delete($elem['id'])) {

                    $this->connection->rollbackTransaction();

                    throw new \Exception(implode("\n", $this->tokenService->getErrors()));
                }

            }

            $result = true;
        }

        $this->connection->commitTransaction();

        return $result;
    }

    public function getUserById(): array
    {
        $result = [];

        $id = $this->serviceRequest['values']['id'];

        if (empty($id)) {
            throw new \Exception('The required id field was not passed');
        }

        $token = $this->getRequestToken($this->serviceRequest['headers']['authorization']);
        $tokenInfo = $this->getTokenInfo(['TOKEN' => $token]);
        $tokenId = $tokenInfo[$token]['id'];

        if (!$tokenId) {
            throw new \Exception('Token not valid');
        }

        $user = Main\UserTable::getList([
            'filter' => [
                'ID' => $id
            ],
            'select' => ['*']
        ])->fetch();

        if ($user) {
            $result = $user;
        }

        return $result;
    }

    /**
     * @param array $context
     * @return array
     * @throws \Exception
     */
    public function auth(): array
    {
        $result = '';

        $authCredentials = $this->getAuthCredentials();

        if (empty($authCredentials)) {

            throw new \Exception('Invalid login or password');
        }

        if ($authCredentials['login'] == self::LOGIN && $authCredentials['password'] == self::PASSWD) {

            $token = bin2hex(random_bytes(32));

        } else {
            throw new \Exception('Invalid login or password');
        }

        if (!$this->tokenService->add(['TOKEN' => $token])) {

            throw new \Exception(implode("\n", $this->tokenService->getErrors()));
        }

        $result = [
            'token' => $token
        ];

        return $result;
    }

    /**
     * @return array
     */
    public function getRequest(): array
    {
        $result = [];

        $request = Context::getCurrent()->getRequest();

        if ($request) {

            $result['headers']['authorization'] = (!empty($request->getHeader('authorization'))) ? $request->getHeader('authorization') : '';

            if ($request->isPost()) {

                $result['values'] = $request->getPostList();

            } else {

                $result['values'] = $request->getQueryList();
            }
        }

        return $result;
    }

    /**
     * @return array|array[]
     */
    public function getAuthCredentials(): array
    {
        $result = [];

        if (!empty($this->serviceRequest)) {
            $result = [
                'login' => $this->serviceRequest['values']['login'],
                'password' => $this->serviceRequest['values']['password']
            ];
        }

        return $result;
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getTokenInfo(array $filter): array
    {
        $result = [];

        $tokenElem = $this->tokenService->getRepository()::getList([
            'filter' => $filter,
            'select' => ['*']
        ]);

        foreach ($tokenElem as $elem) {
            $result[$elem['TOKEN']] = [
                'id' => $elem['ID'],
                'user_id' => $elem['USER_ID'],
                'token' => $elem['TOKEN']
            ];
        }

        return $result;
    }

    /**
     * @param string $headAuth
     * @return string
     */
    public function getRequestToken(string $headAuth): string
    {
        $result = '';

        $authToken = str_replace("Bearer ", "", $headAuth);

        if (!empty($authToken)) {

            $result = $authToken;
        }

        return $result;
    }
}