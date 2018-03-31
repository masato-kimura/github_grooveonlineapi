<?php
namespace main\model\dto;

class AlbumDto
{
	private static $instance = null;

	private $album_id;
	private $artist_id;
	private $album_name;
	private $kana;
	private $english;
	private $same_names;
	private $mbid_itunes;
	private $mbid_lastfm;
	private $url_itunes;
	private $url_lastfm;
	private $image_url;
	private $image_small;
	private $image_medium;
	private $image_large;
	private $image_extralarge;
	private $release_itunes;
	private $copyright_itunes;
	private $genre_itunes;

	private $page;
	private $limit;
	private $sort;

	private $arr_list = array();

	private function __construct(){}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function set_album_id($val)
	{
		$this->album_id = $val;
		return true;
	}
	public function get_album_id()
	{
		return $this->album_id;
	}

	public function set_artist_id($val)
	{
		$this->artist_id = $val;
		return true;
	}
	public function get_artist_id()
	{
		return $this->artist_id;
	}

	public function set_album_name($val)
	{
		$this->album_name = $val;
		return true;
	}
	public function get_album_name()
	{
		return $this->album_name;
	}

	public function set_kana($val)
	{
		$this->kana = $val;
		return true;
	}
	public function get_kana()
	{
		return $this->kana;
	}

	public function set_english($val)
	{
		$this->english = $val;
		return true;
	}
	public function get_english()
	{
		return $this->english;
	}

	public function set_same_names($val)
	{
		$this->same_names = $val;
		return true;
	}
	public function get_same_names()
	{
		return $this->same_names;
	}

	public function set_mbid_itunes($val)
	{
		$this->mbid_itunes = $val;
	}
	public function get_mbid_itunes()
	{
		return $this->mbid_itunes;
	}

	public function set_mbid_lastfm($val)
	{
		$this->mbid_lastfm = $val;
	}
	public function get_mbid_lastfm()
	{
		return $this->mbid_lastfm;
	}

	public function set_url_itunes($val)
	{
		$this->url_itunes = $val;
	}
	public function get_url_itunes()
	{
		return $this->url_itunes;
	}

	public function set_url_lastfm($val)
	{
		$this->url_lastfm = $val;
	}
	public function get_url_lastfm()
	{
		return $this->url_lastfm;
	}

	public function set_image_url($val)
	{
		$this->image_url = trim($val);
	}
	public function get_image_url()
	{
		return $this->image_url;
	}

	public function set_image_small($val)
	{
		$this->image_small = trim($val);
	}
	public function get_image_small()
	{
		return $this->image_small;
	}

	public function set_image_medium($val)
	{
		$this->image_medium = trim($val);
	}
	public function get_image_medium()
	{
		return $this->image_medium;
	}

	public function set_image_large($val)
	{
		$this->image_large = trim($val);
	}
	public function get_image_large()
	{
		return $this->image_large;
	}

	public function set_image_extralarge($val)
	{
		$this->image_extralarge = trim($val);
	}
	public function get_image_extralarge()
	{
		return $this->image_extralarge;
	}

	public function set_release_itunes($val)
	{
		$this->release_itunes = $val;
	}
	public function get_release_itunes()
	{
		return $this->release_itunes;
	}

	public function set_copyright_itunes($val)
	{
		$this->copyright_itunes = $val;
	}
	public function get_copyright_itunes()
	{
		return $this->copyright_itunes;
	}

	public function set_genre_itunes($val)
	{
		$this->genre_itunes = $val;
	}
	public function get_genre_itunes()
	{
		return $this->genre_itunes;
	}

	public function set_page($val)
	{
		$this->page = $val;
		return true;
	}
	public function get_page()
	{
		return $this->page;
	}

	public function set_limit($val)
	{
		$this->limit = $val;
		return true;
	}
	public function get_limit()
	{
		return $this->limit;
	}

	public function set_sort($val)
	{
		$this->sort = $val;
		return true;
	}
	public function get_sort()
	{
		return $this->sort;
	}

	public function set_arr_list($val)
	{
		$this->arr_list = $val;
	}
	public function get_arr_list()
	{
		return $this->arr_list;
	}

}