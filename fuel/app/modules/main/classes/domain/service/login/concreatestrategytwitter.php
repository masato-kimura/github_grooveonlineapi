<?php
namespace main\domain\service\login;

class ConcreateStrategyTwitter implements \main\domain\service\login\LoginStrategy
{
	public function get_user_info()
	{
		$dao = new \main\model\dao\UserDao();
		return $dao->get_userinfo_by_oauth();
	}

	public function login()
	{
		// login_hash値を取得
		$login_hash = \main\domain\service\LoginService::generate_login_hash();

		// データベースにログイン情報を登録, 同時にlogin_dtoにセット
		$login_dao = new \main\model\dao\LoginDao();
		$login_dao->set_login($login_hash);

		return true;
	}

	public function logout()
	{

	}
}