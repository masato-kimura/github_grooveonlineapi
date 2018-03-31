<?php
namespace main\model\dto;

class ReviewMusicDto
{
	private static $instance = null;

	private $review_id;
	private $review_user_id;
	private $artist_id;
	private $artist_name;
	private $album_id;
	private $album_name;
	private $track_id;
	private $track_name;
	private $other_id;
	private $other_name;
	private $link;
	private $review;
	private $is_delete;

	private $arr_list;
	private $page = 1;
	private $offset = 0;
	private $limit;
	private $sort;
	private $about;
	private $about_id;
	private $star;

	private $rank_from_date;
	private $rank_to_date;
	private $count;
	private $search_word;


	private function __construct() {

	}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function set_review_id($val)
	{
		$this->review_id = $val;
	}
	public function get_review_id()
	{
		return $this->review_id;
	}

	public function set_review_user_id($val)
	{
		$this->review_user_id = $val;
	}
	public function get_review_user_id()
	{
		return $this->review_user_id;
	}

	public function set_artist_id($val)
	{
		$this->artist_id = $val;
	}
	public function get_artist_id()
	{
		return $this->artist_id;
	}

	public function set_artist_name($val)
	{
		$this->artist_name = $val;
	}
	public function get_artist_name()
	{
		return $this->artist_name;
	}

	public function set_album_id($val)
	{
		$this->album_id = $val;
	}
	public function get_album_id()
	{
		return $this->album_id;
	}

	public function set_album_name($val)
	{
		$this->album_name = $val;
	}
	public function get_album_name()
	{
		return $this->album_name;
	}

	public function set_track_id($val)
	{
		$this->track_id = $val;
	}
	public function get_track_id()
	{
		return $this->track_id;
	}

	public function set_track_name($val)
	{
		$this->track_name = $val;
	}
	public function get_track_name()
	{
		return $this->track_name;
	}

	public function set_other_id($val)
	{
		$this->other_id = $val;
	}
	public function get_other_id()
	{
		return $this->other_id;
	}

	public function set_other_name($val)
	{
		$this->other_name = $val;
	}
	public function get_other_name()
	{
		return $this->other_name;
	}

	public function set_link($val)
	{
		$this->link = $val;
	}
	public function get_link()
	{
		return $this->link;
	}

	public function set_review($val)
	{
		$this->review = $val;
	}
	public function get_review()
	{
		return $this->review;
	}

	public function set_arr_list($val)
	{
		$this->arr_list = $val;
	}
	public function get_arr_list()
	{
		return $this->arr_list;
	}

	public function set_page($val)
	{
		$this->page = $val;
	}
	public function get_page()
	{
		return $this->page;
	}

	public function set_offset($val)
	{
		$this->offset = $val;
	}
	public function get_offset()
	{
		return $this->offset;
	}

	public function set_limit($val)
	{
		$this->limit = $val;
	}
	public function get_limit()
	{
		return $this->limit;
	}

	public function set_sort($val)
	{
		$this->sort = $val;
	}
	public function get_sort()
	{
		return $this->sort;
	}

	public function set_about($val)
	{
		$this->about = $val;
	}
	public function get_about()
	{
		return $this->about;
	}

	public function set_about_id($val)
	{
		$this->about_id = $val;
	}
	public function get_about_id()
	{
		return $this->about_id;
	}

	public function set_star($val)
	{
		$this->star = $val;
	}
	public function get_star()
	{
		return $this->star;
	}

	public function set_is_delete($val)
	{
		$this->is_delete = $val;
	}
	public function get_is_delete()
	{
		return $this->is_delete;
	}

	public function set_rank_from_date($val)
	{
		$this->rank_from_date = $val;
	}
	public function get_rank_from_date()
	{
		return $this->rank_from_date;
	}

	public function set_rank_to_date($val)
	{
		$this->rank_to_date = $val;
	}
	public function get_rank_to_date()
	{
		return $this->rank_to_date;
	}

	public function set_count($val)
	{
		$this->count = $val;
	}
	public function get_count()
	{
		return $this->count;
	}

	public function set_search_word($val)
	{
		$this->search_word = $val;
	}
	public function get_search_word()
	{
		return $this->search_word;
	}

}
