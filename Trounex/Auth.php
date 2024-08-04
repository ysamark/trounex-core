<?php
/**
 * @version 2.0
 * @author Ysamark
 *
 * @keywords Trouter, Trounex, php framework
 * -----------------
 * @package Trounex
 *
 * MIT License
 *
 * Copyright (c) 2020 Ysare
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace Trounex;

use App\Server;
use PDOException;
use App\Utils\Env;
use Trounex\Application\Model;

class Auth {
  /***********
   * Auth
   * *********
   *
   * authentication helper set
   *
   */
  use Auth\Config;
  use Auth\Encrypt;
  use Auth\TokenSourceReader;
  /**
   *
   * @var array
   *
   * authentication configurations
   * it should contain whole the application authentication
   * configurations such as:
   *
   * User       model Ref (default App\Models\User)
   * Role       model Ref (default App\Models\Role)
   * Token      model Ref (default App\Models\Token)
   * Permission model Ref (default App\Models\Permission)
   *
   * and its respective properties as defined below
   */
  private static $configurations = [
    /**
     * @var array
     */
    'models' => [
      'user' => [
        'name' => 'App\Models\User',
        'props' => [
          'primaryKey' => 'id',
          'role_id' => 'role_id',
          'password' => 'password',
          'username' => [
            'username'
          ],
        ]
      ],

      'role' => [
        'name' => 'App\Models\Role',
        'props' => [
          'primaryKey' => 'id',
          'description' => 'description',
          'name' => 'name',
          'key' => 'key',
        ]
      ],

      'permission' => [
        'name' => 'App\Models\Permission',
        'props' => [
          'primaryKey' => 'id',
          'description' => 'description',
          'name' => 'name',
          'key' => 'key',
        ]
      ],

      'token' => [
        'name' => 'App\Models\User',
        'props' => [
          'primaryKey' => 'id',
          'content' => 'content',
        ]
      ],
    ]
  ];

  /**
   * @var string
   */
  private const TOKEN_COOKIE_NAME = '__Host-data-$0-auth';

  /**
   * @var string
   *
   * 5 Days
   */
  private const TOKEN_COOKIE_EXPIRE_TIME = ((3600 * 24) * 5);

  /**
   * @method boolean
   *
   * verify if a model reference is valid and the class refers to a model
   *
   */
  private static function validModelRef (string $modelRef) {
    return (boolean)(
      class_exists ($modelRef)
      && in_array (Model::class, class_parents ($modelRef))
    );
  }

  /**
   * @method boolean
   *
   * verify if a model reference is valid and the class refers to a model and
   * the given object is an instance of it
   *
   */
  private static function validateSameModel (string $modelRef, $modelObject) {
    return (boolean)(
      self::validModelRef ($modelRef)
      && is_object ($modelObject)
      && ( null
        || get_class ($modelObject) == $modelRef
        || in_array ($modelRef, class_parents (get_class ($modelObject)))
      )
    );
  }

  /**
   * @method boolean
   *
   * verify if a token data is valid
   *
   */
  private static function validTokenData ($tokenData) {
    return is_array ($tokenData) && isset ($tokenData ['userId']);
  }

  /**
   * @method mixed
   *
   * attempt to login by given user credentials
   *
   * the first argument should be either a string or an array
   * if its a string, assume that is the given user name the there is
   * a second argument that should be the user password,
   * and in case of the first argument be an array, assume that
   * it contains the properties for both username and password
   *
   * $user = [
   *    username => string
   *    password => string
   * ]
   *
   */
  public static function attempt ($user) {
    if (is_array ($user)) {
      $username = Helper::getArrayProp($user, 'username');
      $password = Helper::getArrayProp($user, 'password');
    }

    if (is_string ($user)) {
      $username = $user;
      $password = func_num_args () >= 2 ? func_get_arg (1) : null;
    }

    $User = self::getConf('models.user.name');
    $idProp = self::getConf('models.user.props.primaryKey');
    $usernameProps = self::getConf('models.user.props.username');
    $passwordProp = self::getConf('models.user.props.password');

    $usernameProps = is_array ($usernameProps)
      ? $usernameProps
      : [$usernameProps];

    $usernameProps = array_filter ($usernameProps, function ($prop) {
      return is_string ($prop) && !empty ($prop);
    });

    if (self::validModelRef ($User) && count ($usernameProps) >= 1) {
      $usernamePropsLen = count ($usernameProps);
      $userRequest = $User::where([$usernameProps[0] => $username]);

      for ($i = 1; $i < $usernamePropsLen; $i++) {
        $userRequest->orWhere([$usernameProps [$i] => $username]);
      }

      $user = $userRequest->first();

      if (!$user) {
        return [
          'type' => 'error',
          'message' => 'user-not-found'
        ];
      }

      if (password_verify($password, $user->$passwordProp)) {
        if (!isset ($_SESSION)) {
          session_start ();
        }

        $_SESSION['user'] = ['id' => $user->$idProp];

        $fiveDaysAfter = (string)((int)date('d') + 5);

        if (strlen ($fiveDaysAfter) < 2) {
          $fiveDaysAfter = ('0' . $fiveDaysAfter);
        }

        $authTokenData = self::generateToken ([
          'userId' => $user->$idProp,
          'userIp' => $_SERVER ['REMOTE_ADDR'],
          'createdAt' => date ('H:i:s Y/m/d'),
          'expires' => join ('', [date ('H:i:s Y/m/'), $fiveDaysAfter])
        ]);

        // setcookie(self::getAuthCookieName (), $authTokenData, time() + self::TOKEN_COOKIE_EXPIRE_TIME, '/', (Server::isHttps () ? null : Server::Get ('name')), true, true);
        Cookie::set ('auth', $authTokenData);

        return [
          'type' => 'success',
          'user' => $user,
          'token' => $authTokenData,
          'message' => 'user-authenticated'
        ];
      }

      return [
        'type' => 'error',
        'message' => 'wrong-password'
      ];
    }
  }

  /**
   * @method void
   *
   * undo user authentication, remove user authentication data from session and cookie storage
   *
   */
  public static function undo ($redirect = false) {
    $authCookieName = self::getAuthCookieName ();

    $_SESSION ['user'] = ['id' => null];
    setcookie ($authCookieName, '.', time() + self::TOKEN_COOKIE_EXPIRE_TIME, '/', (Server::isHttps () ? null : Server::Get ('name')), true, true);

    $redirectFunctionName = is_bool ($redirect)
      ? 'redirect_back'
      : 'redirect_to';

    if ($redirect) {
      return call_user_func_array ($redirectFunctionName, func_get_args ());
    }
  }

  /**
   * @method void
   *
   * undo user authentication, remove user authentication data from session and cookie storage
   *
   */
  public static function unAuthenticate () {
    return forward_static_call_array ([self::class, 'undo'], func_get_args ());
  }

  /**
   * @method mixed
   *
   * attempt to login by given user credentials
   *
   */
  public static function authenticate () {
    return forward_static_call_array ([self::class, 'attempt'], func_get_args ());
  }

  /**
   * @method boolean|array
   *
   * verify if there is an authenticated user across current request
   *
   */
  public static function authenticated () {
    $userId = self::getUserIdFromAuthData ();
    $User = self::getConf ('models.user.name');

    if (!self::validModelRef ($User)) {
      return;
    }

    $userFetch = $User::where ([ 'id' => $userId ]);

    if ($userFetch->count () >= 1) {
      return $userFetch->first ();
    }

    return false;
  }

  /**
   * @method mixed
   *
   * get the applied role for the given user
   *
   * @return App\Models\Role
   *
   */
  public static function role ($user) {
    $User = self::getConf('models.user.name');
    $Role = self::getConf('models.role.name');
    $roleIdProp = self::getConf('models.user.props.role_id');

    if (self::validateSameModel ($User, $user)
        && self::validModelRef ($Role)) {
      if (isset ($user->$roleIdProp)) {
        $role = $Role::find($user->$roleIdProp);

        if ($role) {
          return $role;
        }
      }
    }
  }

  /**
   * @method mixed
   *
   * get a user by primary key
   *
   * @return App\Models\User
   *
   */
  public static function findUser ($userId) {
    $User = self::getConf('models.user.name');

    if (self::validModelRef ($User)) {
      return $User::find ($userId);
    }
  }

  /**
   * @method mixed
   *
   * get the applied permissions for the given user
   *
   */
  public static function permissions ($user) {
    $userRole = self::role ($user);

    if (is_object ($userRole)) {
      return $userRole->allPermissions ();
    }
  }

  /**
   * @method mixed
   *
   * grant a given permission for the given role referenced by id
   *
   * E.g:
   * Auth::grantPermission('edit:user', 'admin')
   *
   */
  public static function grantPermissionToRoleById (string $permissionKey, $roleId) {
    $Role = self::getConf ('models.role.name');

    if (self::validModelRef ($Role) && is_numeric ($roleId)) {
      $role = $Role::find ($roleId);

      if ($role) {
        return self::grantPermissionToRole ($permissionKey, $role);
      }
    }
  }

  /**
   * @method mixed
   *
   * grant a given permission for the given role referenced by key
   *
   * E.g:
   * Auth::grantPermission('edit:user', 'admin')
   *
   */
  public static function grantPermissionToRoleByKey (string $permissionKey, string $roleKey) {
    $Role = self::getConf ('models.role.name');
    $roleKeyProp = self::getConf ('models.role.props.key');

    if (self::validModelRef ($Role)) {
      $role = $Role::where ([$roleKeyProp => $roleKey])->first();

      if ($role) {
        return self::grantPermissionToRole ($permissionKey, $role);
      }
    }
  }

  /**
   * @method mixed
   *
   * grant a given permission for the given role object
   *
   * E.g:
   * Auth::grantPermission('edit:user', 'admin')
   *
   */
  public static function grantPermissionToRole (string $permissionKey, $role) {
    $Role = self::getConf ('models.role.name');
    $Permission = self::getConf ('models.permission.name');
    $permissionPrimaryKey = self::getConf ('models.permission.props.primaryKey');
    $permissionKeyProp = self::getConf ('models.permission.props.key');

    if (self::validateSameModel ($Role, $role)
        && self::validModelRef ($Permission)) {
      $permissionRequest = Permission::where ([
        $permissionKeyProp => $permissionKey
      ]);

      if ($permissionRequest->count () >= 1) {
        $role->permissions ()
          ->sync ([ $permissionRequest->first()->$permissionPrimaryKey ]);

        return true;
      }
    }
  }

  /**
   * @method mixed
   *
   * grant a given permission for the given user object
   *
   * E.g:
   * Auth::grantPermission('edit:user', $user)
   *
   */
  public static function grantPermissionToUser (string $permissionKey, $user) {
    $User = self::getConf ('models.user.name');

    if (self::validateSameModel ($User, $user)) {
      return self::grantPermissionToRole ($permissionKey, self::role ($user));
    }
  }

  /**
   * @method mixed
   *
   * grant a given permission for the given user by its id
   *
   * E.g:
   * Auth::grantPermission('edit:user', 1)
   *
   */
  public static function grantPermissionToUserById (string $permissionKey, $userId) {
    $User = self::getConf ('models.user.name');

    if (self::validModelRef ($User)) {
      return self::grantPermissionToUser ($permissionKey, $User::find($userId));
    }
  }

  /**
   * @method boolean
   *
   * verify if a permission was granted for the given user
   *
   */
  public static function permissionGranted (string $permissionKey, $user) {
    $userRole = self::role ($user);

    $permissionKeyProp = self::getConf('models.permission.props.key');

    if (is_object ($userRole)) {
      $userPermissions = $userRole->permissions ()
        ->where ([$permissionKeyProp => $permissionKey]);

      if ($userPermissions->count () >= 1) {
        return true;
      }
    }

    return false;
  }
}
