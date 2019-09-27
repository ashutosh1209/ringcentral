<?php
namespace Application\Model;

class Index {
   public $ID;
   public $token_type;
   public $access_token ;
   public $expires_in;
   public $expire_time;
   public $refresh_token;
   public $refresh_token_expires_in;
   public $refresh_token_expire_time;
   public $scope;
   public $owner_id;

   public function exchangeArray($data) {
      $this->ID = (!empty($data['ID'])) ? $data['ID'] : null;
      $this->token_type = (!empty($data['token_type'])) ? $data['token_type'] : null;
      $this->access_token = (!empty($data['access_token'])) ? $data['access_token'] : null;
      $this->expires_in = (!empty($data['expires_in'])) ? $data['expires_in'] : null;
      $this->expire_time = (!empty($data['expire_time'])) ? $data['expire_time'] : null;
      $this->refresh_token = (!empty($data['refresh_token'])) ? $data['refresh_token'] : null;
      $this->refresh_token_expires_in = (!empty($data['refresh_token_expires_in'])) ? $data['refresh_token_expires_in'] : null;
      $this->refresh_token_expire_time = (!empty($data['refresh_token_expire_time'])) ? $data['refresh_token_expire_time'] : null;
      $this->scope = (!empty($data['scope'])) ? $data['scope'] : null;
      $this->owner_id = (!empty($data['owner_id'])) ? $data['owner_id'] : null;
   }

}
