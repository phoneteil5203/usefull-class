<?php

// 自封一个分页类
namespace csl\framework;
class Page
{
	protected $total;//总条数
	protected $page;//当前页
	protected $limit;//每页条数
	protected $totalPage;//总页数
	public $url;//链接url

	public function __construct($total,$limit)
	{
		$this->total = $total < 1 ? 1 : $total;
		$this->limit = $limit < 1 ? 1 : $limit;
		$this->totalPage = ceil($this->total / $this->limit);
		$this->getPage();//获取当前页
		$this->getUrl();//获取当前不带page的url
		$this->uploadUrl();
	}


	// 类外调用分页url
	public function useUrl()
	{
		return  [   'first' => $this->first(),
					'last'  => $this->last(),
					'prev'  => $this->prev(),
					'next'  => $this->next()
			    ];
	}

	// 获取分页用的limit
	public function sqlLimit()
	{
		$str = ($this->page - 1) * $this->limit .','. $this->limit;
		return $str;
	}

	// 使用水平分页时
	public function showPage()
	{
		if($this->limit >= 1){
			$count = ceil($this->limit/2);
		}else{
			$count = 1;
		}
		
	}

	// 为空或大于总页数时上传url
	protected function uploadUrl()
	{
		if(empty($_GET['page'])){
			$first = $this->first();
			header ("location:$first");
		}
		if($_GET['page'] > $this->totalPage){
			$last = $this->last();
			header("location:$last");
		}
	}
	// 获取当前页
	protected function getPage()
	{
		$this->page = empty($_GET['page']) ? 1 : $_GET['page'] ;
		if($this->page < 1){
			$this->page = 1;
		}elseif($this->page > $this->totalPage){
			$this->page = $this->totalPage;
		}else{
			$this->page;
		}
	}

	//拼接跳转用的url无page
	protected function getUrl()
	{
		$url = $_SERVER['REQUEST_SCHEME'] . '://';//协议
		$url .= $_SERVER['HTTP_HOST'];//地址
		$url .= ':' . $_SERVER['SERVER_PORT'];//端口
		$data = parse_url($_SERVER['REQUEST_URI']);
		$url .= $data['path'];//文件
		
		//处理参数
		if(!empty($data['query'])){
			parse_str($data['query'],$arr);
			unset($arr['page']);//干掉page
			$url .= '?'.http_build_query($arr);
		}
		//如果只含有page干掉后剩?
		$url = rtrim($url,'?');
		$this->url = $url;
	}

	// 拼接链接url加page
	protected function setUrl($page)
	{
		if(strpos($this->url,'?') !== false ){
			return $this->url . "&page=$page";
		}else{
			return $this->url ."?page=$page";
		}
	} 

	// 首页
	protected function first()
	{
		return $this->setUrl(1);
	}

	// 尾页
	protected function last()
	{
		return $this->setUrl($this->totalPage);
	}

	// 上一页
	protected function prev()
	{
		$prev = $this->page -1 < 1 ? 1 : $this->page - 1;
		return $this->setUrl($prev); 
	}

	// 下一页
	protected function next()
	{
		$next = $this->page +1 > $this->totalPage ? $this->totalPage : $this->page + 1;
		return $this->setUrl($next);
	}
}