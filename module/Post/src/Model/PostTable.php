<?php

namespace Post\Model;

use Laminas\Db\Sql\Select;
use Laminas\Form\Form;
use Post\Model\Post;
use Post\Service\PostService;
use Post\Service\TimeService;
use RuntimeException;
use Laminas\Db\TableGateway\TableGatewayInterface;
class PostTable{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway){
        $this->tableGateway = $tableGateway;
    }
    public function fetchAll(){
        return $this->tableGateway->select();
    }
    public function getPost($id){
        $id = (int) $id;
        $formset = $this ->tableGateway->select(['id' => $id]);
        $row = $formset->current();
        if (!$row){
            throw new RuntimeException(
                sprintf("Coudn't find the record whit id %d", $id)
            );
        }
        return $row;
    }
    public function savePost(Post $post){
        $data = [
            'title' => $post->title,
            'description' => $post->description,
            'user' => $post->user,
            'image' => $post->image,
        ];
        
        $id = (int) $post->id;
        if ($id === 0){
            date_default_timezone_set("Asia/Tehran");
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->tableGateway->insert($data);
            return;
        }
         try{
             $this->getPost($id);
         } catch(RuntimeException $e){
             throw new RuntimeException(sprintf("Can't update te Record whit id %d", $id));
         }
        $this->tableGateway->update($data,['id' => $id]);
    }

    public function deletePost ($id){
        $this->tableGateway->delete(['id' => (int)$id]);
    }

}