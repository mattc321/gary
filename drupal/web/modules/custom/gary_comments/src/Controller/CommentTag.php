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
    $to = 'mcampbell@ashlandfod.coop';

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

    switch ($host_entity_bundle) {
      case 'tasks':
      break;

      case 'projects':
      case 'opportunities':

      $node_title = $entity->getCommentedEntity()->title->valuem;
      $account_title = $entity->getCommentedEntity()->get('field_account_reference')->getEntity()->title->value;
      $body = $entity->get('comment_body')->value;
      $node_id =  $entity->getCommentedEntity()->id();

      $params = array(
        'uemail' => $to,
        'poster' => $from->getEmail(),
        'poster_name' => $from->getDisplayName(),
        'body' => $body,
        'bundle' => $host_entity_bundle,
        'node_title' => $node_title,
        'node_id' => $node_id,
        'account_title' => $account_title,
      );

      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'gary_comments';
      $key = 'comment_project_tag';
      $params['values'] = $values;
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, 'cascadiamatt@gmail.com', $send);
      if ($result['result'] !== true) {
        $messenger = \Drupal::messenger();
        $messenger->addMessage('An error happened and the notification was not sent', $messenger::TYPE_WARNING);
      }

      break;

      case 'tasks':
      break;
    }

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
