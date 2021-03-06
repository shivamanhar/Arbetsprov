<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller{
	
	function __construct()
	{
		parent::__construct();
		
		$this->load->model('search_model');
		$this->load->spark('gravatar_helper/1.2');
	}
	
	public function _remap()
	{
		// Initierar data, urldecodar om man valt att söka från URL eller utan javascript (submit)
		$site			= $data['site']				= $this->uri->rsegment(2, 'all');
		$search_string	= $data['search_string']	= urldecode(trim($this->uri->rsegment(3, '')));
		
		// Om siten inte finns i config/search_settings.php gå till base_url
		if( ! (array_key_exists($site, $this->search_model->search_engines) || $site === 'all'))
			redirect("/");
			
		// Ser om vi postat utan javascript enablat
		if($this->input->post('submit') !== FALSE)
			$this->submit($site);
			
		// Bygger meny
		$data['menu_array'] = array(
			'all' => array('active' => FALSE, 'title' =>  'Sök på alla sökmotorer samtidigt!'),
			'google' => array('active' => FALSE, 'title' =>  'Sök på Google!'),
			'yahoo' => array('active' => FALSE, 'title' =>  'Sök på Yahoo!'),
			'bing' => array('active' => FALSE, 'title' =>  'Sök på Bing!')
		);
		
		// Sätter aktivt menyval
		$data['menu_array'][$site]['active'] = TRUE;
		
		// Testar Sparks (getsparks.org) och Gravatar_helper
		$data['gravatar'] = Gravatar_helper::from_email('nicholas.ruunu@gmail.com', 'G', 150);
		
		// Gör en sökning om vi har någonting i söksträngen
		if($search_string !== '')
			$data['search_results']['results'] = $this->search($site, $search_string);

		// Echoar ut sökresultaten vi requestar med ajax
		if($this->input->is_ajax_request())
			echo $this->load->view('search_results', $data['search_results'], TRUE);
		else
			$this->load->view('search_index', $data);
	}
	
	private function search($site, $search_string)
	{
		return ($site === 'all')
			? $this->search_model->get_combined_results($search_string)
			: $this->search_model->get_results($site, $search_string);
	}
	
	private function submit($site)
	{
		$search_string = ($this->input->post('search_string') !== FALSE)
			? urlencode($this->input->post('search_string')) : '';
		
		// Redirectar för att URL-strukturen ska bli snygg och delbar utan javascript
		redirect("/search/{$site}/{$search_string}");
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/search.php */