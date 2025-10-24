<?php
interface UserInterface {
    public function register($data);
    public function login($username, $password);
    public function updateProfile($data);
    public function enable2FA();
    public function verify2FA($code);
    public function readAll();
    public function getUserById($id);
}
?>