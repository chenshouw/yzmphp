<?php
/**
 * application.class.php YZMPHP应用程序创建类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-08-29 
 */
 
class application {
	
	/**
	 * 构造函数
	 */
	public function __construct() { 
		yzm_base::load_sys_class('debug', '', 0);
		register_shutdown_function(array('debug','fatalerror'));
		set_error_handler(array('debug','catcher'));
		$param = yzm_base::load_sys_class('param');
		define('ROUTE_M', $param->route_m());
		define('ROUTE_C', $param->route_c());
		define('ROUTE_A', $param->route_a());
		$this->init();
	}
	

	/**
	 * 调用件事
	 */
	private function init() {
		$controller = $this->load_controller();
		if (method_exists($controller, ROUTE_A)) {
			if (preg_match('/^[_]/i', ROUTE_A)) {
				self::halt('You are visiting the action is to protect the private action');
			} else {
				call_user_func(array($controller, ROUTE_A));
				if(APP_DEBUG){
					debug::stop();
					if(!defined('DEBUG_HIDDEN')) debug::message();
				} 
			}
		} else {
			self::halt('Action does not exist : '.ROUTE_A);
		}
	}
	

	/**
	 * 加载控制器
	 * @param string $filename
	 * @param string $m
	 * @return obj
	 */
	private function load_controller($filename = '', $m = '') {
		if (empty($filename)) $filename = ROUTE_C;
		if (empty($m)) $m = ROUTE_M;
		if (!is_dir(APP_PATH.$m)) self::halt('module does not exist : '.$m);
		$filepath = APP_PATH.$m.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.$filename.EXT;
		if (is_file($filepath)) {
			include $filepath;
			if(class_exists($filename)){
				return new $filename;
			}else{
				self::halt('Controller does not exist : '.$filename);
 			}
		} else {
			self::halt('Controller does not exist : '.$filename);
		}
	}
	

	/**
	 *  提示信息页面跳转
	 *
	 * @param     string  $msg      消息提示信息
	 * @param     string  $gourl    跳转地址
	 * @param     int     $limittime  限制时间
	 * @return    void
	 */
	public static function showmsg($msg, $gourl, $limittime) {
		$gourl = empty($gourl) ? HTTP_REFERER : $gourl;
		$stop = $gourl!='stop' ? false : true;
		include(YP_PATH.'core'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.'message.tpl');
	}


	/**
	 * 打开调式模式情况下, 输出致命错误
	 *
	 * @param     string  $msg      提示信息
	 * @param     string  $detailed	详细信息
	 * @param     string  $type     错误类型 1:php 2:mysql
	 * @return    void
	 */
	public static function fatalerror($msg, $detailed = '', $type = 1) {
		if(ob_get_length() !== false) @ob_end_clean();
		include(YP_PATH.'core'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.'error.tpl');
		exit();
	}

	
	/**
	 *  输出错误提示
	 *
	 * @param     string  $msg      提示信息
	 * @return    void
	 */
	public static function halt($msg) {
		if(ob_get_length() !== false) @ob_end_clean();
		header('HTTP/1.1 404 Not Found');
        header('Status:404 Not Found');
		include(YP_PATH.'core'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.'halt.tpl');
		exit();
	}
}