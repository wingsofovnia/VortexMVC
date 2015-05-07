<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 05-May-15
 * Time: 13:54
 */

namespace vortex\backbone\vertebrae;

use vortex\auth\AuthManager;
use vortex\auth\DatabaseUserProvider;
use vortex\backbone\VertebraInterface;
use vortex\database\PDOConnection;
use vortex\facade\Auth;
use vortex\facade\Database;
use vortex\hashing\GenericHasher;
use vortex\http\Request;
use vortex\http\Response;
use vortex\storage\StorageFactory;
use vortex\utils\Config;

class FacadeVertebra implements VertebraInterface {
    public function process(Request $request, Response $response) {
        $config = Config::getInstance();

        // Connection
        $driver = $config->database->driver('mysql');
        $host = $config->database->host('localhost');
        $user = $config->database->user('root');
        $password = $config->database->password('');
        $db = $config->database->db('VortexMVC');

        $connection = new PDOConnection($driver, $host, $user, $password, $db);
        Database::setConnection($connection);

        // Auth manager
        $table = $config->auth->table('user');
        $id = $config->auth->column->id('email');
        $pass = $config->auth->column->password('password');
        $storageDriverName = $config->auth->provider->storage('vortex\storage\drivers\SessionStorageDriver');

        $storageDriver = StorageFactory::build($storageDriverName);
        $userProvider = new DatabaseUserProvider($connection, new GenericHasher(), $table, $id, $pass);

        $authManger = new AuthManager($storageDriver, $userProvider);
        Auth::setAuthManager($authManger);

    }
}