<?php
/**
 * Pagination class
 * ------------------------------------ 
 * Generates pagination
 * 
 */

namespace Core\Pagination;

class Pagination {
	public $itemNumber;
	public $total;
	public $pageNumber;
	public $currentPage = 1;
	public $params = "?";

	public function __construct($total, $itemNumber){
		$this->total      = $total;
		$this->itemNumber = $itemNumber;
		$this->pageNumber = ceil($total / $itemNumber);

		if(isset($_GET['page']) && $_GET['page'] > 1){
			$this->currentPage = intval($_GET['page']);
		}
	}

	public function make(){
		$take = $this->itemNumber;

		if($this->currentPage > 1){
			$skip = ($this->currentPage - 1) * $this->itemNumber;

		}else{
			$skip = 0;
		}

		return ['skip' => $skip, 'take' => $take];
	}

	public function appends($params = null){
		unset($params['page']);
		if($params){
			foreach ($params as $key => $value) {
				$tab[] = $key.'='.$value;
			}

			$this->params = '?'.implode('&', $tab).'&';
		}

		return $this;
	}

	/**
	 * Create a CSS Bootstrap pagination
	 * @return string HTML pagination
	 */
	public function links(){

		if($this->pageNumber < 2){
			return '';
		}
		$pagination = '';
		$previous = '';
		$next = '';
		$middle = '';
		$start = '<nav><ul class="pagination justify-content-center">';

		# Determine previous class
		if($this->currentPage == 1){
			$previous = "disabled";
		}

		# Determine next class
		if($this->currentPage == $this->pageNumber){
			$next = "disabled";
		}

		# Generates previous page
		$pagination .= '<li class="page-item '.$previous.'">';
		if($this->currentPage > 1){
			$pagination .= '<a class="page-link" href="'.$this->params.'page='.($this->currentPage - 1).'" aria-label="Previous">';
		}else{
			$pagination .= '<a class="page-link" href="#" aria-disabled="true">';
		}
		$pagination .= '<span aria-hidden="true">&laquo;</span>';
		$pagination .= '</a>';
		$pagination .= '</li>';

		# Generates numbers page
		if($this->pageNumber < 5){
			for($i = 1; $i <= $this->pageNumber; $i++){
				if($i != $this->currentPage){
					$pagination .= '<li class="page-item"><a class="page-link" href="'.$this->params.'page='.$i.'">'.$i.'</a></li>';
				}else{
					$pagination .= '<li class="page-item active"><a class="page-link" href="'.$this->params.'page='.$i.'">'.$i.'<span class="sr-only">(current)</span></a></li>';
				}
			}
		}else{
			if($this->currentPage < ($this->pageNumber - 1)){
				$pagination .= '<li class="page-item active"><a class="page-link" href="'.$this->params.'page='.$this->currentPage.'">'.$this->currentPage.'<span class="sr-only">(current)</span></a></li>';
				$pagination .= '<li class="page-item"><a class="page-link" href="'.$this->params.'page='.($this->currentPage + 1).'">'.($this->currentPage + 1).'</a></li>';
			}else{
				$pagination .= '<li class="page-item"><a class="page-link" href="'.$this->params.'page=1">1</a></li>';
				$pagination .= '<li class="page-item"><a class="page-link" href="'.$this->params.'page=2">2</a></li>';
			}

			$pagination .= '<li class="page-item disabled"><span>...</span></li>';

			if($this->currentPage < ($this->pageNumber - 1)){
				$pagination .= '<li class="page-item"><a class="page-link" href="'.$this->params.'page='.($this->pageNumber - 1).'">'.($this->pageNumber - 1).'</a></li>';
				$pagination .= '<li class="page-item"><a class="page-link" href="'.$this->params.'page='.$this->pageNumber.'">'.$this->pageNumber.'</a></li>';
			}elseif($this->currentPage == ($this->pageNumber - 1)){
				$pagination .= '<li class="page-item active"><a class="page-link" href="'.$this->params.'page='.$this->currentPage.'">'.$this->currentPage.'<span class="sr-only">(current)</span></a></li>';
				$pagination .= '<li class="page-item"><a class="page-link" href="'.$this->params.'page='.$this->pageNumber.'">'.$this->pageNumber.'</a></li>';
			}else{
				$pagination .= '<li class="page-item"><a class="page-link" href="'.$this->params.'page='.($this->pageNumber - 1).'">'.($this->pageNumber - 1).'</a></li>';
				$pagination .= '<li class="page-item active"><a class="page-link" href="'.$this->params.'page='.$this->currentPage.'">'.$this->currentPage.'<span class="sr-only">(current)</span></a></li>';
			}
		}

		# Generates next page
		$pagination .= '<li class="page-item '.$next.'">';
		if($this->currentPage != $this->pageNumber){
			$pagination .= '<a class="page-link" href="'.$this->params.'page='.($this->currentPage + 1).'" aria-label="Next">';
		}else{
			$pagination .= '<a class="page-link" href="#" aria-disabled="true">';
		}

		$pagination .= '<span aria-hidden="true">&raquo;</span>';
		$pagination .= '</a>';
		
		$pagination .= '</li>';
		
		$end = '</ul></nav>';

		return $start.$pagination.$end;
	}
}