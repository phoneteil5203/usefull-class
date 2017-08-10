<?php
namespace csl\framework;

// 封装文件上传父类

class Upload
{
	protected $UploadDir = './public/upload';//上传图片保存的路径
	public $uploadedPath; //上传后的文件路径
	protected $makeDateDir = true;//是否生成日期目录
	protected $fileSize = 2*1024*1024;//规定上传文件大小2M
	protected $makeRandName = true;//是否生成随机文件名
	protected $errno;//错误号
	protected $error = [
		-1 => '没有上传信息',
		-2=>'上传目录不存在',
		-3 => '上传目录不具备读写权限',
		-4 =>'上传超过规定',
		-5 => '文件后缀不符合要求',
		-6 => '文件MIME类型不符合规定',
		-7 => '不是上传文件',
		-8 => '文件最后一步上传失败了',
		 0 => '上传成功',
		 1 =>'上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值',
		 2=>'上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
		 3=>'文件只有部分被上传。',
		 4=>'没有文件被上传。',
		 6=>'找不到临时文件夹。PHP 4.3.10 和 PHP 5.0.3 引进',
		 7=>'文件写入失败',

	];
	protected $fileInfo;//上传文件的信息
	protected $mime = ['image/jpeg','image/bmp','image/png','image/gif'];//允许的mime类型
	protected $type = ['jpg','jpeg','gif','bmp','png'];//允许的后缀类型

	public function __construct(array $data = null)
	{
		if(!empty($data)){
			if(property_exists(__CLASS__,$key)){
				$this->$key = $value;
			}
		}

		$this->handleUploadDir();//处理文件保存路径的格式
	}


	public function upload($key)
	{
		// 检查上传信息
		$this->checkFiles($key);
		// 检查保存目录
		$this->checkUploadedFile($this->UploadDir);
		// 检查标准错误
		$this->checkSystemError();
		// 检查自定义类型type mime 大小
		$this->checkFileInfo();
		// 检查是否是post上传文件
		$this->postFile();
		// 将上传文件移动到指定目录
		$this->moveFile();

		//返回最终目录
		return $this->uploadedPath;
	}
	/*将window目录格式转换为linux格式，拼接/保证路径正确*/
	protected function handleUploadDir()
	{
		$this->UploadDir = str_replace('\\','/',$this->UploadDir);
		$this->UploadDir = rtrim($this->UploadDir,'/') . '/';
	}

	/*检查上传信息*/
	protected function checkFiles($key)
	{
		if($_FILES[$key]['error'] == 0){
			$this->fileInfo = $_FILES[$key];
		}else{
			$this->errNo = -1;
			exit("$this->error['-1']");
		}
	}

	/*检查保存目录*/
	protected function checkUploadedFile($dir)
	{
		//检查是不是文件
		if(!is_dir($dir)){
			if(!mkdir($dir,0777,true)){
				$this->errNo = -2;
				exit("$this->error[-2]");
			}
		}
		//检查文件权限
		if(!is_readable($dir) || !is_writable($dir)){
			if(!chmod($dir,0777)){
				$this->errNo = -3;
				exit("$this->error[-3]");
			}
		}
	}

	/*检查文件系统error*/
	protected function checkSystemError()
	{
		$this->errNo = $this->fileInfo['error'];
		if($this->errNo != 0){
			exit("$this->error[$this->errNo]");
		}
	}

	/*检查文件信息*/
	protected function checkFileInfo()
	{
		//判断文件是否超过大小
		if($this->fileInfo['size'] > $this->fileSize){
			$this->errNo = -4;
			exit("$this->error[-5]");
		}

		//判断是否为允许的mime类型
		$type = $this->fileInfo['type'];
		if(!in_array($type,$this->mime)){
			$this->errNo = -6;
			exit("$this->error[-6]");
		}

		//判断是否为允许的文件后缀
		$type = $this->fileType();
		if(!in_array($type,$this->type)){
			$this->errNo = -5;
			exit("$this->error[-5]");
		}

		return true;
	}

	/*获取上传文件的type类型*/
	protected function fileType()
	{
		$path = pathinfo($this->fileInfo['name']);
		return $path['extension'];
	}

	/*判断是否是post提交*/
	protected function postFile()
	{
		if(!is_uploaded_file($this->fileInfo['tmp_name'])){
			$this->errNo = -7;
			exit("$this->error[-7]");
		}
		
		return true;
	}

	/*上传文件*/
	protected function moveFile()
	{
		//是否采用日期目录
		if($this->makeDateDir){
			$path = $this->UploadDir . date('Y/m/d') . '/';
			$this->checkUploadedFile($path);
		}
		//是否文件名随机
		if($this->makeRandName){
			$path .= uniqid() . '.' . $this->extName($this->fileInfo['name']);
		}else{
			$path .= $this->fileInfo['name'];
		}
		//上传文件
		if(!move_uploaded_file($this->fileInfo['tmp_name'],$path)){
			$this->errNo = -8;
			exit("$this->error[-8]");
		}

		$this->uploadedPath = $path; 
	}

	/*查找文件后缀名*/
	protected function extName($file)
	{
		return pathinfo($file)['extension'];
	}
}