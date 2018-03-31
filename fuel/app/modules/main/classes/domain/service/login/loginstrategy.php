<?php
namespace main\domain\service\login;

interface LoginStrategy
{
	public function get_user_info();
	public function login();
	public function logout();
}