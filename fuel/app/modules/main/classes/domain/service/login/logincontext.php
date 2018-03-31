<?php
namespace main\domain\service\login;

class LoginContext
{
	private $strategy;
	
	public function __construct(LoginStrategy $strategy)
	{
		$this->strategy = $strategy;
	}
	
	/**
	 * UserDtoにユーザ情報が入っていることが条件
	 * 
	 */
	public function get_user_info()
	{
		return $this->strategy->get_user_info();
	}
	
	public function login()
	{
		return $this->strategy->login();
	}
	
	public function logout()
	{
		return $this->strategy->logout();
	}
}
