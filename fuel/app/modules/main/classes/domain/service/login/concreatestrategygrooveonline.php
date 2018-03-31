<?php
namespace main\domain\service\login;
use main\model\dao\LoginDao;
use main\domain\service\LoginService;

class ConcreateStrategyGrooveonline implements \main\domain\service\login\LoginStrategy
{
	public function get_user_info()
	{
		\Log::debug('[start]'. __METHOD__);

		$dao = new \main\model\dao\UserDao();
		return $dao->get_userinfo_by_email_and_password();
	}

	public function login()
	{
		\Log::debug('[start]'. __METHOD__);

		# login_hash値を取得
		$login_hash = LoginService::generate_login_hash();

		# データベースにログイン情報を登録, 同時にlogin_dtoにセット
		$login_dao = new LoginDao();
		$login_dao->set_login($login_hash);

		return true;
	}

	public function logout()
	{

	}
}