<?php

include 'simple_html_dom.php';

function time_float()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}


class Curl
{

	public $session = array();
	private $post = array();
	public $html = '';
	public $time;
	private $page = 1;

	private $type;
	public $type_arr = array(
		1 => 'Corporation Name',
		2 => 'Limited Liability Company/Limited Partnership Name',
		3 => 'Entity Number');


	public $info_fields = array(
		'name'                  => 'Entity Name',
		'number'                => 'Entity Number',
		'date_filed'            => 'Date Filed',
		'status'                => 'Status',
		'jurisdiction'          => 'Jurisdiction',
		'address'               => 'Entity Address',
		'address_csz'           => 'Entity City, State, Zipcode',
		'address_city'          => 'Entity City',
		'address_zipcode'       => 'Entity Zipcode',
		'agent'                 => 'Agent for Service of Process',
		'agent_address'         => 'Agent Address',
		'agent_address_csz'     => 'Agent City, State, Zipcode',
		'agent_address_city'    => 'Agent City',
		'agent_address_zipcode' => 'Agent Zipcode'
		);


	function get_session()
	{
		if ($this->html === '')
		{
			$ch = curl_init('http://kepler.sos.ca.gov/');
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36');
			$this->html = curl_exec($ch);
			curl_close($ch);
		}

		preg_match('/<input[^>]*name=["\']__EVENTVALIDATION["\'][^>]*value=["\']([^"\']*)["\'][^>]*>/', $this->html, $n);
		preg_match('/<input[^>]*name=["\']__VIEWSTATE["\'][^>]*value=["\']([^"\']*)["\'][^>]*>/', $this->html, $l);
        
		$this->session['__EVENTVALIDATION'] = isset($n[1]) ? $n[1] : '';
		$this->session['__VIEWSTATE']       = isset($l[1]) ? $l[1] : '';

		return $this;
	}


	public function get_count()
	{
		preg_match('#id="ctl00_content_placeholder_body_SearchResults1_TextInfoCorp1_TextInfoSearchResultCounts1_Label_RowCount">(.*)</span>#', $this->html, $m);
        
		return (int) trim(str_replace(',', '', @$m[1]));
	}


	function type($type)
	{
		$type = $this->type_arr[$type];
		$this->type = $type;

		$this->post = array(
			'__EVENTTARGET'                                                             => 'ctl00$content_placeholder_body$BusinessSearch1$RadioButtonList_SearchType$0',
			'__EVENTARGUMENT'                                                           => '',
			'__LASTFOCUS'                                                               => '',
			'__VIEWSTATE'                                                               => $this->session['__VIEWSTATE'],
			'__VIEWSTATEENCRYPTED'                                                      => '',
			'__EVENTVALIDATION'                                                         => $this->session['__EVENTVALIDATION'],
			'ctl00$content_placeholder_body$BusinessSearch1$RadioButtonList_SearchType' => $type,
			'ctl00$content_placeholder_body$BusinessSearch1$TextBox_NameSearch'         => '');

		return $this;
	}


	function keyword($kw)
	{

		$this->post = array(
			'__EVENTTARGET'                                                             => '',
			'__EVENTARGUMENT'                                                           => '',
			'__LASTFOCUS'                                                               => '',
			'__VIEWSTATE'                                                               => $this->session['__VIEWSTATE'],
			'__VIEWSTATEENCRYPTED'                                                      => '',
			'__EVENTVALIDATION'                                                         => $this->session['__EVENTVALIDATION'],
			'ctl00$content_placeholder_body$BusinessSearch1$RadioButtonList_SearchType' => $this->type,
			'ctl00$content_placeholder_body$BusinessSearch1$TextBox_NameSearch'         => $kw,
			'ctl00$content_placeholder_body$BusinessSearch1$Button_Search'              => 'Search');

		return $this;
	}


	function page($page)
	{
		$this->post = array(
			'__EVENTTARGET'        => 'ctl00$content_placeholder_body$SearchResults1$GridView_SearchResults_Corp',
			'__EVENTARGUMENT'      => 'Page$' . $page,
			'__VIEWSTATE'          => $this->session['__VIEWSTATE'],
			'__VIEWSTATEENCRYPTED' => '',
			'__EVENTVALIDATION'    => $this->session['__EVENTVALIDATION']);

		return $this;
	}


	function detail($idx)
	{
		$this->post = array(
			'__EVENTTARGET'        => 'ctl00$content_placeholder_body$SearchResults1$GridView_SearchResults_Corp',
			'__EVENTARGUMENT'      => 'DetailCorp$' . ($idx - 1),
			'__VIEWSTATE'          => $this->session['__VIEWSTATE'],
			'__VIEWSTATEENCRYPTED' => '',
			'__EVENTVALIDATION'    => $this->session['__EVENTVALIDATION']);

		return $this;
	}

	function post($func = '', $arg = '')
	{
		if (empty($this->session))
		{
			$this->get_session();
		}

		if (!($func === 'page' && $arg === 1))
		{
			$this->$func($arg);
		}

		$cookie = 'cookies.txt';
		$ch = curl_init('http://kepler.sos.ca.gov');
		curl_setopt($ch, CURLOPT_REFERER, 'http://kepler.sos.ca.gov');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36');
		$this->html = curl_exec($ch);
		curl_close($ch);

		$this->get_session();

		return $this;
	}


	function search($type, $keyword, $page, $id)
	{
		$this->time = time_float();

		$this->post('type', $type);
		$this->post('keyword', $keyword);
		$this->post('page', $page);
		$this->post('detail', $id);

		$this->time = time_float() - $this->time;

		return $this;
	}


	function parse()
	{
		$dom = str_get_html($this->html);
        
        $arr = array();
        
		$rows = $dom->find('#ctl00_content_placeholder_body_EntityDetail1_DetailsView1', 0);
        
        if ($rows !== null)
        {
            $rows = $rows->find('tr');
            
            if ($rows !== null)
            {
        		$keys = array_keys($this->info_fields);
        		foreach ($keys as $i => $v)
        		{
        			if (in_array($v, array('address_city', 'address_zipcode', 'agent_address_city', 'agent_address_zipcode')))
        			{
        				unset($keys[$i]);
        			}
        		}
        		$keys = array_values($keys);
        		
        		foreach ($rows as $i => $r)
        		{
					$key   = $keys[$i];
					$value = trim($r->find('td', 1)->innertext());
					$value = preg_replace('(\s+)', ' ', $value);
					$value = htmlspecialchars_decode($value);
					$value = str_replace('&#39;', '\'', $value);

        			if ($key === 'date_filed')
        			{
        				$value = date('Y-m-d', strtotime($value));
        			}

        			$arr[$key] = $value;
        		}

        		list($arr['address_city'], $arr['address_zipcode']) = array_pad(explode(' CA ', $arr['address_csz']), 2, '');
        		list($arr['agent_address_city'], $arr['agent_address_zipcode']) = array_pad(explode(' CA ', $arr['agent_address_csz']), 2, '');
            }
        }

		return $arr;
	}


	function html()
	{
		return $this->html;
	}

}
