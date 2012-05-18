<?php  if ( ! defined('SITE')) exit('No direct script access allowed');
class Model_Paginator
{
	public $num_links = 2;
	
	public function validate($start, $per_page)
	{
		$start = (int) $start;
		return ( $start % $per_page > 0 )? $start - ($start % $per_page) : $start;
	}

// start - текущая страница
// total - всего страниц	
	public function creat($current, $total, $uri_segment = 3, $per_page = 0)
	{
		if ( $per_page != 0 ) {
			$start = 0;
			$page = $per_page;
			$total -= ($total > $per_page AND ($i = $total % $per_page) > 0) ? $i : $per_page;
		} else {
			$start = 1;
			$page = 1;
		}
		$uri = $this->getUri($uri_segment);
		
		$p = '<div class="pagination"><div class="next-prev">';
		// Next and previus button
		if ( $current > $start ) {
			$p .= '<a href="'.sprintf($uri, ($current - $page)).'">← Назад</a>';
		} else {
			$p .= '<span>← Назад</span>';
		}
		
		if ( $current < $total ) {
			$p .= '<a href="'.sprintf($uri, ($current + $page)).'">Вперед →</a>';
		} else {
			$p .= '<span>Вперед →</span>';
		}
		$p .= '</div><div class="pages">';
		
		// First page
		if ( $current - $start > ( $this->num_links * $page ) ) {
			$p .= '<a href="'.sprintf($uri, $start).'">1</a> ... ';
		}
		// All pages
		$i = $current - ( $this->num_links * $page );
		$i = ($i < $start) ? $start : $i;
		
		$j = $current + ( $this->num_links * $page );
		$j = ($j > $total) ? $total : $j;
		
		for (; $i <= $j; $i+=$page )
		{
			$p .= '<a href="'.sprintf($uri, $i).'"'.($i == $current?' class="active"':'').'>'
			.( ($i + $per_page) / $page ).'</a> ';
		}
		
		// Last page
		if ( $current < $total - ( $this->num_links * $page )) {
			$p .= '... <a href="'.sprintf($uri, $total).'">'.( ($total + $per_page) / $page).'</a> ';
		}
		
		return $p.'</div></div>';
	}
	
	private function getUri($uri_segment)
	{
		$segments = array_pad(Load::Router()->getSegments(), $uri_segment+1, '');
		
		$uri = BASEURL;
		foreach ( $segments as $key => $val ) $uri .= ($key == $uri_segment ? '%d' : $val).'/';
		
		return $uri;
	}
}