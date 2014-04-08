<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

	public $url = "http://banking-innovation.org/index.php/2011-06-05-21-12-47";

	public $baseurl = "http://banking-innovation.org";

	public $data=array();

	public function index()
	{

		// $html = $this->curl->simple_get($this->url);

		// $html = str_replace('  ', '', $html);
		// $html = str_replace("\n", '', $html);
		// $html = str_replace("\r", '', $html);
		// $html = str_replace("\t", '', $html);
		// $html = preg_replace('/<body(.*?)>/', '', $html);
		// $html = str_replace(' align="center"', '', $html);

		$this->html_dom->loadHTMLFile($this->url);



// echo self::next_page();die;
		$i=0;

		do{

			$this->html_dom->loadHTMLFile($this->url);

			//get each page
			$arr = $this->html_dom->find('.readon');
			foreach($arr as $val){
				$each_page_url = $this->baseurl.$val->getAttr('href');

				$this->html_dom->loadHTMLFile($each_page_url);

				$title = $this->html_dom->find('.itemTitle',0)->getInnerText();

				// $about = $this->html_dom->find('.itemIntroText',0)->getInnerText();
				// $about = self::extract_about($about);
				$about = $this->html_dom->find('.itemIntroText',0);
				if(!empty($about)){
					$about = $about->getInnerText();
					$about = self::extract_about($about);
				}else{
					$about='';
				}


				$tags = $this->html_dom->find('.itemTags',0)->getInnerText();
				$tags = self::extract_tags($tags);


				$company_site = $this->html_dom->find('.itemExtraFieldsValue',3);
				if(!empty($company_site)){
					$company_site = $company_site->getInnerText();
					$company_site = self::extract_url($company_site);
				}else{
					$company_site='';
				}

				$country = $this->html_dom->find('.itemExtraFieldsValue',2)->getInnerText();
				$country = self::extract_country($country);

				$features = $this->html_dom->find('.list',0)->getInnerText();
				$features = self::extract_features($features);


				$this->data[$i] = array();
				$this->data[$i]['title'] = $title;
				$this->data[$i]['about'] = $about;
				$this->data[$i]['tags'] = $tags;
				$this->data[$i]['country'] = $country;
				$this->data[$i]['company_site'] = $company_site;
				$this->data[$i]['features'] = $features;


				self::display_data($this->data[$i]);

				$i++;
			}

		}while($this->url=self::next_page());

		// self::display_data($this->data);
	}


	public function next_page(){
		$this->html_dom->loadHTMLFile($this->url);
		$domElement = $this->html_dom->find('.pagination-next',0)->find('a',0);
		// $str = $domElement->find('a',0);


		if(count($domElement))
			return $this->baseurl.$domElement->getAttr('href');

		// foreach ($tags as $tag) {
		//      $features .=  '\n '.$tag->nodeValue;
		// }


		return false;
	}

	public function extract_features($str){
		$features = '';

		$dom = new DOMDocument();
		$dom->loadHTML($str);

		$tags = $dom->getElementsByTagName('li');
		foreach ($tags as $tag) {
		     $features .=  '\n '.$tag->nodeValue;
		}

		return $features;
	}


	public function extract_about($str){
		$about = '';

		$dom = new DOMDocument();
		$dom->loadHTML($str);

		$tags_inner = $dom->getElementsByTagName('p');
		foreach ($tags_inner as $tag_inner) {
		     $about =  $tag_inner->nodeValue;
		     // break;
		}

		return $about;
	}

	public function extract_tags($str){
		$tags = '';

		$dom = new DOMDocument();
		$dom->loadHTML($str);

		$tags_inner = $dom->getElementsByTagName('a');
		foreach ($tags_inner as $tag_inner) {
		     $tags .=  '\n '.$tag_inner->nodeValue;
		}

		return $tags;
	}


	public function display_data($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	}


	public function extract_country($str){
		$country = '';

		$dom = new DOMDocument();
		$dom->loadHTML($str);

		$tags = $dom->getElementsByTagName('a');
		foreach ($tags as $tag) {
		     $country .=  ' '.$tag->nodeValue;
		}

		return $country;
	}

	public function extract_url($str){

		$href='';

		$dom = new DOMDocument();
		$dom->loadHTML($str);

		$tags = $dom->getElementsByTagName('a');
		foreach ($tags as $tag) {
		     $href =  $tag->getAttribute('href');
		     break;
		}

		return $href;
	}
}

