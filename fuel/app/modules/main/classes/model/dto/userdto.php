<?php
namespace main\model\dto;

class UserDto
{
	private static $instance = null;

	private $user_id = null;
	private $disp_user_id = null;
	private $user_name = null;
	private $date = null;
	private $first_name = null;
	private $last_name = null;
	private $password = null;
	private $password_digits = null;
	private $password_org = null;
	private $email = null;
	private $link = null;
	private $gender = null;
	private $birthday = null;
	private $birthday_year = '';
	private $birthday_month = '';
	private $birthday_day = '';
	private $birthday_secret = '0';
	private $old = '';
	private $old_secret = 0;
	private $locale = null;
	private $country = null;
	private $postal_code = null;
	private $pref = null;
	private $locality = null;
	private $street = null;
	private $profile_fields = null;
	private $facebook_url = '';
	private $twitter_url = '';
	private $google_url = '';
	private $site_url = '';
	private $instagram_url = '';
	private $auth_type = '';
	private $oauth_id = null;
	private $picture_url = '';
	private $last_login = null;
	private $last_logout = null;
	private $is_decided = null;
	private $decide_date = '';
	private $is_leaved = null;
	private $leave_date = '';
	private $member_type = null;
	private $created_at = '';
	private $updated_at = '';
	private $is_deleted = null;

	private $invited_by = null; // 招待種別
	private $target_id = null;  // 招待されるユーザID
	private $invite_id = null;  // 招待するユーザID

	private $favorite_artists = array();

	private function __construct()
	{}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function set_id($str)
	{
		$this->user_id = $str;
		return true;
	}
	public function get_id()
	{
		return $this->user_id;
	}

	public function set_user_id($str)
	{
		$this->user_id = $str;
		return true;
	}
	public function get_user_id()
	{
		return $this->user_id;
	}

	public function set_disp_user_id($str)
	{
		$this->disp_user_id = $str;
		return true;
	}
	public function get_disp_user_id()
	{
		return $this->disp_user_id;
	}

	public function set_user_name($str)
	{
		$this->user_name = $str;
		return true;
	}
	public function get_user_name()
	{
		return $this->user_name;
	}

	public function set_date($str)
	{
		$this->date = $str;
		return true;
	}
	public function get_date()
	{
		return $this->date;
	}

	public function set_first_name($str)
	{
		$this->first_name = $str;
		return true;
	}
	public function get_first_name()
	{
		return $this->first_name;
	}

	public function set_last_name($str)
	{
		$this->last_name = $str;
		return true;
	}
	public function get_last_name()
	{
		return $this->last_name;
	}

	public function set_password($str)
	{
		$this->password = $str;
		return true;
	}
	public function get_password()
	{
		return $this->password;
	}

	public function set_password_digits($str)
	{
		$this->password_digits = $str;
		return true;
	}
	public function get_password_digits()
	{
		return $this->password_digits;
	}

	public function set_password_org($str)
	{
		$this->password_org = $str;
		return true;
	}
	public function get_password_org()
	{
		return $this->password_org;
	}

	public function set_email($str)
	{
		$this->email = $str;
		return true;
	}
	public function get_email()
	{
		return $this->email;
	}

	public function set_link($str)
	{
		$this->link = $str;
		return true;
	}
	public function get_link()
	{
		return $this->link;
	}

	public function set_gender($str)
	{
		$this->gender = $str;
		return true;
	}
	public function get_gender()
	{
		return $this->gender;
	}

	public function set_birthday($str)
	{
		$this->birthday = $str;
		return true;
	}
	public function get_birthday()
	{
		return $this->birthday;
	}

	public function set_birthday_year($str)
	{
		$this->birthday_year = $str;
		return true;
	}
	public function get_birthday_year()
	{
		return $this->birthday_year;
	}

	public function set_birthday_month($str)
	{
		$this->birthday_month = $str;
		return true;
	}
	public function get_birthday_month()
	{
		return $this->birthday_month;
	}

	public function set_birthday_day($str)
	{
		$this->birthday_day = $str;
		return true;
	}
	public function get_birthday_day()
	{
		return $this->birthday_day;
	}

	public function set_old($str)
	{
		$this->old = $str;
		return true;
	}
	public function get_old()
	{
		return $this->old;
	}

	public function set_birthday_secret($str)
	{
		$this->birthday_secret = $str;
		return true;
	}
	public function get_birthday_secret()
	{
		return $this->birthday_secret;
	}

	public function set_old_secret($str)
	{
		$this->old_secret = $str;
		return true;
	}
	public function get_old_secret()
	{
		return $this->old_secret;
	}

	public function set_locale($str)
	{
		$this->locale = $str;
		return true;
	}
	public function get_locale()
	{
		return $this->locale;
	}

	public function set_country($str)
	{
		$this->country = $str;
		return true;
	}
	public function get_country()
	{
		return $this->country;
	}

	public function set_postal_code($str)
	{
		$this->postal_code = $str;
		return true;
	}
	public function get_postal_code()
	{
		return $this->postal_code;
	}

	public function set_pref($str)
	{
		$this->pref = $str;
		return true;
	}
	public function get_pref()
	{
		return $this->pref;
	}

	public function set_locality($str)
	{
		$this->locality = $str;
		return true;
	}
	public function get_locality()
	{
		return $this->locality;
	}

	public function set_street($str)
	{
		$this->street = $str;
		return true;
	}
	public function get_street()
	{
		return $this->street;
	}

	public function set_profile_fields($str)
	{
		$this->profile_fields = $str;
		return true;
	}
	public function get_profile_fields()
	{
		return $this->profile_fields;
	}

	public function set_facebook_url($str)
	{
		$this->facebook_url = $str;
		return true;
	}
	public function get_facebook_url()
	{
		return $this->facebook_url;
	}

	public function set_twitter_url($str)
	{
		$this->twitter_url = $str;
		return true;
	}
	public function get_twitter_url()
	{
		return $this->twitter_url;
	}

	public function set_google_url($str)
	{
		$this->google_url = $str;
		return true;
	}
	public function get_google_url()
	{
		return $this->google_url;
	}

	public function set_instagram_url($str)
	{
		$this->instagram_url = $str;
		return true;
	}
	public function get_instagram_url()
	{
		return $this->instagram_url;
	}

	public function set_site_url($str)
	{
		$this->site_url = $str;
		return true;
	}
	public function get_site_url()
	{
		return $this->site_url;
	}

	public function set_auth_type($str)
	{
		$this->auth_type = $str;
		return true;
	}
	public function get_auth_type()
	{
		return $this->auth_type;
	}

	public function set_oauth_id($str)
	{
		$this->oauth_id = $str;
		return true;
	}
	public function get_oauth_id()
	{
		return $this->oauth_id;
	}

	public function set_picture_url($str)
	{
		$this->picture_url = $str;
		return true;
	}
	public function get_picture_url()
	{
		return $this->picture_url;
	}

	public function set_last_login($str)
	{
		$this->last_login = $str;
		return true;
	}
	public function get_last_login()
	{
		return $this->last_login;
	}

	public function set_last_logout($str)
	{
		$this->last_logout = $str;
		return true;
	}
	public function get_last_logout()
	{
		return $this->last_logout;
	}

	public function set_is_decided($str)
	{
		$this->is_decided = $str;
	}
	public function get_is_decided()
	{
		return $this->is_decided;
	}

	public function set_decide_date($str)
	{
		$this->decide_date = $str;
	}
	public function get_decide_date()
	{
		return $this->decide_date;
	}

	public function set_is_leaved($str)
	{
		$this->is_leaved = $str;
	}
	public function get_is_leaved()
	{
		return $this->is_leaved;
	}

	public function set_leave_date($str)
	{
		$this->leave_date = $str;
	}
	public function get_leave_date()
	{
		return $this->leave_date;
	}

	public function set_is_deleted($str)
	{
		$this->is_deleted = $str;
		return true;
	}
	public function get_is_deleted()
	{
		return $this->is_deleted;
	}

	public function set_member_type($str)
	{
		$this->member_type = $str;
		return true;
	}
	public function get_member_type()
	{
		return $this->member_type;
	}

	public function set_invited_by($str)
	{
		$this->invited_by = $str;
		return true;
	}
	public function get_invited_by()
	{
		return $this->invited_by;
	}

	public function set_target_id($str)
	{
		$this->target_id = $str;
		return true;
	}
	public function get_target_id()
	{
		return $this->target_id;
	}

	public function set_invite_id($str)
	{
		$this->invite_id = $str;
		return true;
	}
	public function get_invite_id()
	{
		return $this->invite_id;
	}

	public function set_favorite_artists($str)
	{
		$this->favorite_artists = $str;
		return true;
	}
	public function get_favorite_artists()
	{
		return $this->favorite_artists;
	}

}