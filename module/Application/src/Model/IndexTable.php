<?php

namespace Application\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class IndexTable {

  protected $tableGateway;

  public function __construct(TableGatewayInterface $tableGateway) {
     $this->tableGateway = $tableGateway;
  }

  public function fetchAll() {
     $resultSet = $this->tableGateway->select();
     return $resultSet;
  }

  public function getIndex($id)
  {
      $id = (int) $id;
      $rowset = $this->tableGateway->select(['ID' => $id]);
      $row = $rowset->current();
      if (! $row) {
          throw new RuntimeException(sprintf(
              'Could not find row with identifier %d',
              $id
          ));
      }

      return $row;
  }

  public function saveIndex(Index $index)
  {
      $data = [
          'token_type' => $index->token_type,
          'access_token'  => $index->access_token,
          'expires_in'  => $index->expires_in,
          'expire_time'  => $index->expire_time,
          'refresh_token' => $index->refresh_token,
          'refresh_token_expires_in'  => $index->refresh_token_expires_in,
          'refresh_token_expire_time' => $index->refresh_token_expire_time,
          'scope' => $index->scope,
          'owner_id'  => $index->owner_id
      ];

      $id = (int) $index->id;

      if ($id === 0) {
          $this->tableGateway->insert($data);
          return;
      }

      try {
          $this->getIndex($id);
      } catch (RuntimeException $e) {
          throw new RuntimeException(sprintf(
              'Cannot update album with identifier %d; does not exist',
              $id
          ));
      }

      $this->tableGateway->update($data, ['ID' => $id]);
  }

}
