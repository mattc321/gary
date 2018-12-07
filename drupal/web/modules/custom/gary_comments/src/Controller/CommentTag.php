<?php

namespace Drupal\gary_comments\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;

class CommentTag extends ControllerBase {

  public function AddMessage(EntityInterface $entity, $message = NULL) {
    // ksm($entity->getOwnerId());
    // ksm($entity->get('cid')->getValue());

    $title = $entity->subject->value;
    $body =  $entity->get('comment_body')->value;
    $comment_id =  $entity->get('cid')->value;
    $content_id = $entity->getCommentedEntity()->id();
    $by =  $entity->getOwnerId();
    $to = $this->GetTaggedUser($body);

    $message = Node::create([
       'type' => 'messages',
       'title' => $title,
       'body' => $body,
       'field_tag_comment_id'=> $comment_id,
       'field_tag_content_reference'=> $content_id,
       'field_tag_user_by'=>$by,
       'field_tag_user_to'=>$to,
     ]);

// $node->save();
  }

  public function SendNotification(EntityInterface $entity, $to = NULL, $from = NULL) {
    // ksm($to);
    // ksm($from);
  }

  protected function GetTaggedUser(string $body) {

    $str = explode(" ",$body);

    foreach($str as $k=>$word){
      if(substr($word,0,1)=="@"){
        $uname = trim(substr($word,1));
        $tuser = user_load_by_name($uname);

        if(!empty($tuser)) {
          return $tuser;
        } else {
          return NULL;
        }
      }
    }
  }
}
