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
		$start = '<nav><ul class="pagination">';

		# Determine previous class
		if($this->currentPage == 1){
			$previous = "disabled";
		}

		# Determine next class
		if($this->currentPage == $this->pageNumber){
			$next = "disabled";
		}

		# Generates previous page
		$pagination .= '<li class="'.$previous.'">';
		if($this->currentPage > 1){
			$pagination .= '<a href="?page='.($this->currentPage - 1).'" aria-label="Previous">';
		}
		$pagination .= '<span aria-hidden="true">&laquo;</span>';
		if($this->currentPage > 1){
			$pagination .= '</a>';
		}
		$pagination .= '</li>';

		# Generates numbers page
		if($this->pageNumber < 5){
			for($i = 1; $i <= $this->pageNumber; $i++){
				if($i != $this->currentPage){
					$pagination .= '<li><a href="?page='.$i.'">'.$i.'</a></li>';
				}else{
					$pagination .= '<li class="active"><a href="?page='.$i.'">'.$i.'<span class="sr-only">(current)</span></a></li>';
				}
			}
		}else{
			if($this->currentPage < ($this->pageNumber - 1)){
				$pagination .= '<li class="active"><a href="?page='.$this->currentPage.'">'.$this->currentPage.'<span class="sr-only">(current)</span></a></li>';
				$pagination .= '<li><a href="?page='.($this->currentPage + 1).'">'.($this->currentPage + 1).'</a></li>';
			}else{
				$pagination .= '<li><a href="?page=1">1</a></li>';
				$pagination .= '<li><a href="?page=2">2</a></li>';
			}

			$pagination .= '<li class="disabled"><span>...</span></li>';

			if($this->currentPage < ($this->pageNumber - 1)){
				$pagination .= '<li><a href="?page='.($this->pageNumber - 1).'">'.($this->pageNumber - 1).'</a></li>';
				$pagination .= '<li><a href="?page='.$this->pageNumber.'">'.$this->pageNumber.'</a></li>';
			}elseif($this->currentPage == ($this->pageNumber - 1)){
				$pagination .= '<li class="active"><a href="?page='.$this->currentPage.'">'.$this->currentPage.'<span class="sr-only">(current)</span></a></li>';
				$pagination .= '<li><a href="?page='.$this->pageNumber.'">'.$this->pageNumber.'</a></li>';
			}else{
				$pagination .= '<li><a href="?page='.($this->pageNumber - 1).'">'.($this->pageNumber - 1).'</a></li>';
				$pagination .= '<li class="active"><a href="?page='.$this->currentPage.'">'.$this->currentPage.'<span class="sr-only">(current)</span></a></li>';
			}
		}

		# Generates next page
		$pagination .= '<li class="'.$next.'">';
		if($this->currentPage != $this->pageNumber){
			$pagination .= '<a href="?page='.($this->currentPage + 1).'" aria-label="Next">';
		}
		$pagination .= '<span aria-hidden="true">&raquo;</span>';
		if($this->currentPage != $this->pageNumber){
			$pagination .= '</a>';
		}
		$pagination .= '</li>';
		
		$end = '</ul></nav>';

		return $start.$pagination.$end;
	}
}