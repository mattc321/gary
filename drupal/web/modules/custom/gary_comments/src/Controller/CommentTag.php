<?php

namespace Drupal\gary_comments\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;

class CommentTag extends ControllerBase {

  protected $tagged_users = [];

  protected $error_string = NULL;

  public function CheckForTag(EntityInterface $entity) {

    $body =  $entity->get('comment_body')->value;

    //is there atleast one tag
    if (stripos($body, '@') !==FALSE) {
      //if users are tagged create messages for them
      if (!empty($this->GetTaggedUsers($body))) {
        foreach ($this->tagged_users as $key => $user) {
          $this->AddMessage($entity, $user);
        }
      }

      //check for a tag error
      if (!empty($this->error_string)) {
        $messenger = \Drupal::messenger();
        $messenger->addMessage($this->error_string, $messenger::TYPE_WARNING);
      }
      return true;
    }

    return false;
  }


  public function AddMessage(EntityInterface $entity, $user) {

    $title = $entity->subject->value;
    $body =  $entity->get('comment_body')->value;
    $comment_id =  $entity->get('cid')->value;
    $content_id = $entity->getCommentedEntity()->id();
    $by =  $entity->getOwner();
    $to = $user;

    $message = Node::create([
       'type' => 'messages',
       'title' => $title,
       'body' => $body,
       'field_tag_comment_id'=> $comment_id,
       'field_tag_content_reference'=> $content_id,
       'field_tag_user_by'=>$by,
       'field_tag_user_to'=>$to,
     ]);
     $message->save();
     $this->SendNotification($entity, $to, $by);
  }

  public function SendNotification(EntityInterface $entity, $to, $from) {
    $host_entity_bundle = $entity->getCommentedEntity()->bundle();
    ksm($host_entity_bundle);
  }

  protected function GetTaggedUsers($body) {

    $str = explode(" ",$body);

    $users = [];

    foreach($str as $k=>$word){
      if(substr($word,0,1)=="@"){
        $uname = trim(substr($word,1));
        $tuser = user_load_by_name($uname);
        if(!empty($tuser)) {
          $users[] = $tuser;
        } else {
          $this->error_string = t("You tagged a user that I could not find!");
        }
      }
    }
    $this->tagged_users = $users;
    return $users;
  }
}
