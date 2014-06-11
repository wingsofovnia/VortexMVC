<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 29-May-14
 * Time: 15:41
 */

namespace Vortex;

class Auth {
    public function login($identity, $password) {

    }

    public function logout() {

    }

    public function register($username, $password, $email, $additionalData, $group) {

    }

    public function update($id, $data) {

    }

    public function deleteUser($id) {

    }

    public function forgottenPassword($id) {

    }

    public function forgottenPasswordComplete($id) {

    }

    public function isLogged() {

    }

    public function isAdmin() {

    }

    public function isMemberOf($group) {

    }

    public function usernameExists($name) {

    }

    public function emailExists($email) {

    }

    public function getUserData($id) {

    }

    public function getUsersOfGroup($groupId) {

    }

    public function removeFromGroup($userId) {

    }

    public function createGroup($name, $description, $level) {

    }

    public function updateGroup($id, $data) {

    }

    public function deleteGroup($id) {

    }
} 