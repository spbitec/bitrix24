<?

namespace spbitec\bitrix24;

class oauthClient{
	private static $clientData=[]; 
   private static $accData=[];
   private static $requested="";
   
   const ERROR_BAD_CLIENT=1;
   const ERROR_TOKEN_INVALID=2;
   
   static function getRedirect(){
   	return self::$requested; 
   }

   static function getAuthUrl(){
   	return "https://".self::$clientData['appDomain']."/oauth/authorize/?client_id=".self::$clientData['clientId'];
   }


   static function getToken(){
      if (!$_GET['code']){
         header("Location: ".self::getAuthUrl());
         exit;
      } else {
         $url="https://{$_GET['server_domain']}/oauth/token/?grant_type=authorization_code&client_id=".self::$clientData['clientId']."&client_secret=".self::$clientData['clientKey']."&code={$_GET['code']}";
         $ret=json_decode(@file_get_contents($url), true);
         if (!$ret) throw(new \Exception('Null response',self::ERROR_BAD_CLIENT));
         if ($ret['error']) throw(new \Exception($ret['error'],self::ERROR_BAD_CLIENT));
         self::$accData=$ret;
      }
   }

   static function refreshToken(){
      $url="https://{$_GET['server_domain']}/oauth/token/?grant_type=refresh_token&client_id=".self::$clientData['clientId']."&client_secret=".self::$clientData['clientKey']."&refresh_token=".self::$accData['refresh_token'];
      $ret=json_decode(@file_get_contents($url), true);
      self::$accData=$ret;
   }

   static function checkAuth($clientData, $data, $redir){
   	self::$clientData=$clientData;
      self::$accData=$data;
      
      if (!self::$accData['access_token'] || time()>self::$accData['expires']){

         if (self::$accData['refresh_token'] && time()+60*60*24*28>self::$accData['expires']){
            if ($redir){
               self::getToken();
               self::$requested=1;
            } else {
               throw(new \Exception($ret['Token expired or not exists'],self::ERROR_TOKEN_INVALID));
            }
            
         } else {
            if (self::$accData['access_token'] && time()>self::$accData['expires']){
               self::refreshToken();
               self::$requested=1;
            } else {
               if ($redir || $_GET['code']){
                  self::getToken();
                  self::$requested=1;
               } else {
                  throw(new \Exception($ret['Token expired or not exists'],self::ERROR_TOKEN_INVALID));
               }
            }
         }
      }
      return self::$accData;
   }

	static function getAuthData(){
   	return self::$accData;
   }
}