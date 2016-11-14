<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 20:48
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\FrontendController;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Models\Entities\FrontendUser;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * this controller is concerd so a user can register & login
 *
 * Class LoginController
 * @package Famoser\SyncApi\Controllers
 */
class LoginController extends FrontendController
{
    /**
     * show the login form
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function login(Request $request, Response $response, $args)
    {
        return $this->renderTemplate($response, "login/login", $args);
    }

    /**
     * process the login form post request
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed|static
     */
    public function loginPost(Request $request, Response $response, $args)
    {
        $req = $request->getParsedBody();
        if (isset($req["username"]) && isset($req["password"])) {
            $user = $this->getDatabaseHelper()->getSingleFromDatabase(
                new FrontendUser(),
                "username = :username",
                ["username" => $req["username"]]
            );
            if ($user != null && password_verify($req["password"], $user->password)) {
                $this->setFrontendUser($user);
                return $this->redirect($request, $response, "application_index");
            }
        }
        $args["message"] = "something went wrong with the login :/";
        $args["last_request"] = $req;
        return $this->renderTemplate($response, "login/login", $args);
    }

    /**
     * show the register form
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function register(Request $request, Response $response, $args)
    {
        return $this->renderTemplate($response, "login/register", $args);
    }

    /**
     * register the user if possible, if not display error message and show register form again
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed|static
     * @throws ServerException
     */
    public function registerPost(Request $request, Response $response, $args)
    {
        $req = $request->getParsedBody();
        if (
            isset($req["username"]) &&
            isset($req["email"]) &&
            isset($req["password"]) &&
            $req["password"] == $req["password2"]
        ) {
            $usr = $this->getDatabaseHelper()->getSingleFromDatabase(
                new FrontendUser(),
                "username = :username OR email = :email",
                ["username" => $req["username"], "email" => $req["email"]]
            );
            if ($usr == null) {
                $frontendUser = new FrontendUser();
                $frontendUser->email = $req["email"];
                $frontendUser->password = password_hash($req["password"], PASSWORD_BCRYPT);
                $frontendUser->username = $req["username"];
                $frontendUser->reset_key = md5(rand(0, 100000));
                if (!$this->getDatabaseHelper()->saveToDatabase($frontendUser)) {
                    throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
                }
                return $this->redirect($request, $response, "login");
            } else {
                $args["message"] = "username or email already registered";
            }
        } else {
            $args["message"] = "something went wrong :/ <br/>please double check you've filled out all fields correctly";
        }
        unset($req["password"]);
        unset($req["password2"]);
        $args["last_request"] = $req;
        return $this->renderTemplate($response, "login/register", $args);
    }

    /**
     * show the forgot dialog
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function forgot(Request $request, Response $response, $args)
    {
        return $this->renderTemplate($response, "login/forgot", $args);
    }

    /**
     * send a recover email and display an ambiguous message as a confirmation
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function forgotPost(Request $request, Response $response, $args)
    {
        $req = $request->getParsedBody();
        if (isset($req["username"]) && isset($req["email"])) {
            $user = $this->getDatabaseHelper()->getSingleFromDatabase(
                new FrontendUser(),
                "username = :username AND email = :email",
                ["username" => $req["username"], "email" => $req["email"]]
            );
            if ($user != null) {
                //generate new reset key
                $user->reset_key = substr(md5(rand(0, 100000)), 0, 10);
                if (!$this->getDatabaseHelper()->saveToDatabase($user)) {
                    throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
                }
                //send mail
                mail(
                    $user->email,
                    "password reset on sync api",
                    "hi " . $user->username . ", here's your code to recover your password: " . $user->reset_key
                );
            }
        }
        $args["message"] = "you've probably received an email with a code to reset your password";
        return $this->renderTemplate($response, "login/forgot", $args);
    }

    /**
     * show the recover form
     * 
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function recover(Request $request, Response $response, $args)
    {
        return $this->renderTemplate($response, "login/recover", $args);
    }

    /**
     * assign the user a new password if the reset key is correct
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed|static
     * @throws ServerException
     */
    public function recoverPost(Request $request, Response $response, $args)
    {
        $req = $request->getParsedBody();
        if (isset($req["username"]) && isset($req["authorization_code"]) && isset($req["password"]) && $req["password"] == $req["password2"]) {
            $user = $this->getDatabaseHelper()->getSingleFromDatabase(
                new FrontendUser(),
                "username = :username AND reset_key = :reset_key",
                ["username" => $req["username"], "reset_key" => $req["authorization_code"]]
            );
            if ($user != null) {
                $user->password = password_hash($req["password"], PASSWORD_BCRYPT);
                //generate new reset key
                $user->reset_key = substr(md5(rand(0, 100000)), 0, 10);
                if (!$this->getDatabaseHelper()->saveToDatabase($user)) {
                    throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
                }
                return $this->redirect($request, $response, "login");
            }
        }
        unset($req["password"]);
        unset($req["password2"]);
        $args["last_request"] = $req;
        $args["message"] = "something went wrong :/ <br/>please double check you've filled out all fields correctly";
        return $this->renderTemplate($response, "login/recover", $args);
    }
}
