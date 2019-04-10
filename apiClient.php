<?

namespace spbitec\bitrix24;

class apiClient{
   private static $accData=[];
   
   const ERROR_TOKEN_NOT_EXISTS=1;
   const ERROR_TOKEN_EXPIRED=2;
   
   public static function setToken($accData){
   	self::$accData=$accData;
   }

	private static function makeUrl($method, $data){
      $data['auth']=self::$accData['access_token'];
      $query=http_build_query($data);
      return self::$accData['client_endpoint'].$method."?".$query;
   }

	private static function makeRequest($method, $data=false){
      if (!self::$accData['access_token']) throw(new \Exception('Token not exists',self::ERROR_TOKEN_NOT_EXISTS));
      if (time()>self::$accData['expires']) throw(new \Exception('Token expired',self::ERROR_TOKEN_EXPIRED));
      $url=self::makeUrl($method, $data);
      $ret=json_decode(@file_get_contents($url), true);
      return $ret;
   }

	public static function user_current(){
      return self::makeRequest('user.current');
   }
}