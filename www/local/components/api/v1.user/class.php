<?php

use Bitrix\Main;
use Bitrix\Main\Engine;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response;
use TestCase\UserService;

class apiUserComponent extends UserService implements Controllerable, Main\Errorable
{
    use ErrorableImplementation;

    private $result;

    public function configureActions()
    {
        return [];
    }

    public function executeComponent()
    {
        $this->result = new \Bitrix\Main\Result();

        $action = $this->arParams['ACTION'] . "Action";

        if (!method_exists(__CLASS__, $action)) {

            $this->result->addError(new \Bitrix\Main\Error('Action not found'));
        }

        $this->serviceRequest = $this->getRequest();

        if (empty($this->serviceRequest)) {
            $this->result->addError(new \Bitrix\Main\Error('Request error'));
        }

        if (!$this->result->isSuccess()) {

            $this->response();
        }

        $this->$action();

        $this->response();
    }

    /**
     * Return response
     */
    public function response()
    {
        global $APPLICATION;

        $this->errorCollection = new ErrorCollection();

        if (!$this->result->isSuccess()) {

            $this->errorCollection->add($this->result->getErrors());

            $response = Response\AjaxJson::createError($this->result->getErrorCollection(), $this->result->getData());

        } else {

            $response = Response\AjaxJson::createSuccess($this->result->getData());
        }

        $application = Main\Application::getInstance();
        $application->getContext()->setResponse($response);
        $APPLICATION->restartBuffer();
        $application->end();
    }

    /**
     * @return bool
     */
    public function createUserAction()
    {
        $this->errorCollection = new ErrorCollection();

        $result = new \Bitrix\Main\Result();

        $userInfo = [];

        try {
            $userInfo = $this->createUser();
        } catch (\Exception $e) {
            $this->result->addError(new \Bitrix\Main\Error($e->getMessage()));
        }

        if ($this->result->isSuccess()) {
            $this->result->setData($userInfo);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function getUserByIdAction()
    {
        $userInfo = [];

        try {

            $userInfo = $this->getUserById();

        } catch (\Exception $e) {

            $this->result->addError(new \Bitrix\Main\Error($e->getMessage()));
        }

        if (!empty($userInfo)) {
            $this->result->setData($userInfo);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function removeUserByIdAction()
    {
        try {

            $this->removeUserById();

        } catch (\Exception $e) {

            $this->result->addError(new \Bitrix\Main\Error($e->getMessage()));
        }

        return true;
    }

    /**
     * @return bool
     */
    public function authAction()
    {
        $token = [];

        try {

            $token = $this->auth();

        } catch (\Exception $e) {

            $this->result->addError(new \Bitrix\Main\Error($e->getMessage()));
        }

        $this->result->setData($token);

        return true;
    }
}
