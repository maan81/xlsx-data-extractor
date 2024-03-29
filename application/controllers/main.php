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
				$title = self::remove_spaces($title);

				$about = $this->html_dom->find('.itemIntroText',0);
				if(!empty($about)){
					$about = $about->getInnerText();
					$about = self::extract_about($about);
				}else{
					$about='';
				}


				$tags = $this->html_dom->find('.itemTags',0);
				if(!empty($tags)){
					$tags = $tags->getInnerText();
					$tags = self::extract_tags($tags);
				}else{
					$tags='';
				}

				$company_site = $this->html_dom->find('.itemExtraFieldsValue',3);
				if(!empty($company_site)){
					$company_site = $company_site->getInnerText();
					$company_site = self::extract_url($company_site);
				}else{
					$company_site='';
				}

				$country = $this->html_dom->find('.itemExtraFieldsValue',2);//->getInnerText();
				// $country = self::extract_country($country);
				if(!empty($country)){
					$country = $country->getInnerText();
					$country = self::extract_country($country);
				}else{
					$country='';
				}

				$features = $this->html_dom->find('.list',0);//->getInnerText();
				// $features = self::extract_features($features);
				if(!empty($features)){
					$features = $features->getInnerText();
					$features = self::extract_features($features);
				}else{
					$features='';
				}
// self::display_data($features);die;


				$this->data[$i] = array();
				$this->data[$i]['title'] = $title;
				$this->data[$i]['about'] = $about;
				$this->data[$i]['tags'] = $tags;
				$this->data[$i]['country'] = $country;
				$this->data[$i]['company_site'] = $company_site;
				$this->data[$i]['features'] = $features;


				// self::display_data($this->data[$i]);

				$i++;
			}

		}while($this->url=self::next_page());

		// self::display_data($this->data);

		self::writeExcel();
	}


	public function writeExcel(){
		//load PHPExcel library
		$this->load->library('Excel');
		 
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		 
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("mohamadikhwan.com")
									 ->setLastModifiedBy("mohamadikhwan.com")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated by PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");
		 
		 
		// The title of row
		$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A3', 'S. No.')
		            ->setCellValue('B3', 'Company/Product Name')
		            ->setCellValue('C3', 'About ("Unternehmensbeschreibung)')
		            ->setCellValue('D3', 'Tags')
		            ->setCellValue('E3', 'Website ("Offizielle Webseite")')
		            ->setCellValue('F3', 'Country ("Land")')
		            ->setCellValue('G3', 'Innovative Features');
		 

        // data list
        $i=0;
        foreach($this->data as $val){
			$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.($i+4), (string)($i+1))      // serial number
			            ->setCellValue('B'.($i+4), $val['title'])       // company/product name
			            ->setCellValue('C'.($i+4), $val['about'])       // about
			            ->setCellValue('D'.($i+4), strtoupper($val['tags']))//tags
			            ->setCellValue('E'.($i+4), $val['company_site'])// website
			            ->setCellValue('F'.($i+4), $val['country'])     // country
			            ->setCellValue('G'.($i+4), $val['features']);   // innovative features
            $i++;
        }


		// Rename worksheet (worksheet, not filename)
		$objPHPExcel->getActiveSheet()->setTitle('Sheet1');
		 
		 
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		 
		// Redirect output to a client’s web browser (Excel2007)
		//clean the output buffer
		ob_end_clean();
		 
		//this is the header given from PHPExcel examples. but the output seems somewhat corrupted in some cases.
		//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//so, we use this header instead.
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="Bank-Innovation_Companies.xlsx"');
		header('Cache-Control: max-age=0');
		 
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	}

	public function remove_spaces($str){
		return preg_replace("/\s+/", " ", $str);
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
		     $features .=  $tag->nodeValue.PHP_EOL;
		}

		return $features;
	}


	public function extract_about($str){
		$about = '';

		$dom = new DOMDocument();
		$dom->loadHTML($str);

		$tags_inner = $dom->getElementsByTagName('p');
		foreach ($tags_inner as $tag_inner) {
			$about = $tag_inner->nodeValue;
		    // $about = str_replace('&#13;', '', $about);
		    $about = self::remove_spaces($about);
		     // $about = preg_replace("/&#13;/", " ", $about);
// self::display_data($about);
		    
// self::display_data($about);die;
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
		     $tags .=  $tag_inner->nodeValue.PHP_EOL;
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

