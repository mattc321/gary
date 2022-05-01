<?php

namespace Drupal\gary_comments\Controller;

use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\gary_custom\GaryFunctions;
use Drupal\Core\Ajax;
use Drupal\views\Views;
use Drupal\Core\Ajax\InvokeCommand;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;

class CommentTag extends ControllerBase {

  protected $tagged_users = [];

  protected $error_string = NULL;

  public function buildMessages() {
    $user = \Drupal::currentUser();
    $data = [];
    $viewId = 'my_messages';
    $displayId = 'rest_export_1';
    $arguments = [$user->id()];

    // Get the view
    $result = $this->getView($viewId, $displayId, $arguments);
    if(is_object($result)) {
      $json = $result->jsonSerialize();
      $data = json_decode($json);
    }
    //get the user from picture and the node title
    foreach ($data as $key => $d) {
      $from = User::load($d->field_tag_user_by[0]->target_id);
      $file_uri = $from->get('user_picture')->entity->getFileUri();
      $styled_image_url = ImageStyle::load("user_pic_small")->buildUrl($file_uri);
      $picture = $styled_image_url;
      $data[$key]->field_tag_user_by[0]->image_url = $picture;

      $node = Node::load($d->field_tag_content_reference[0]->target_id);
      $title = is_object($node) ? $node->getTitle() : '';
      $data[$key]->field_tag_content_reference[0]->title = $title;
    }
    // return $data;
    return new JsonResponse($data);
  }

  function markRead($id) {
    $node = Node::load($id);
    $node->set('field_message_read', TRUE);
    $node->save();
    return new Response(0);
  }

  function getView($viewId, $displayId, array $arguments) {
      $result = false;
      $view = Views::getView($viewId);

      if (is_object($view)) {
          $view->setDisplay($displayId);
          $view->setArguments($arguments);
          $view->execute();
          $view_render = $view->render();
          // Render the view
          $renderer = \Drupal::service('renderer');
          $result = $renderer->render($view_render);
      }

      return $result;
  }
  public function checkMessages() {
    $user = \Drupal::currentUser();
    $data = [];
    $viewId = 'my_messages';
    $displayId = 'rest_export_2';
    $arguments = [$user->id()];

    // Get the view
    $result = $this->getView($viewId, $displayId, $arguments);
    // return new JsonResponse($data);
    if(is_object($result)) {
        $json = $result->jsonSerialize();
        $data = json_decode($json);
    }
    return new Response(count($data));
  }

  /**
   * Check to see if the comment body has a tagged user
   * @param EntityInterface $entity The comment entity
   * @return boolean                True if found false if not
   */
  public function CheckForTag(EntityInterface $entity) {

    $body =  $entity->get('comment_body')->value;

    //is there atleast one tag
    if (stripos($body, '@') !==FALSE) {
      //if users are tagged create messages for them
      \Drupal::logger('commentstag')->debug($entity->id());

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


  /**
   * Add a new messahe entity and send a notification
   * @param EntityInterface $entity The comment entity
   * @param mixed          $user   mixed user array of the tagged user
   * @return boolean                return
   */
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
       'created' => $entity->getCreatedTime(),
       'changed' => $entity->getCreatedTime(),
       'field_tag_comment_id'=> $comment_id,
       'field_tag_content_reference'=> $content_id,
       'field_tag_user_by'=>$by,
       'field_tag_user_to'=>$to,
       'field_message_read'=>TRUE,
     ]);
     $message->save();
     $this->SendNotification($entity, $to, $by);
     return;
  }

  /**
   * Prepare and send the notification emails to the tagged users
   * @param EntityInterface $entity The comment entity
   * @param mixed          $to     Mixed user array of the recipient
   * @param mixed          $from   Mixed user array of the comment author
   * @return boolean               return
   */
  public function SendNotification(EntityInterface $entity, $to, $from) {

    //different emails go out for different parent bundles
    $host_entity_bundle = $entity->getCommentedEntity()->bundle();

    //emails in hook_mail
    switch ($host_entity_bundle) {
      case 'projects':
      case 'opportunities':

        $node_title = $entity->getCommentedEntity()->title->value;
        $account_target = $entity->getCommentedEntity()->get('field_account_reference')->target_id;
        $account_title = \Drupal::entityTypeManager()->getStorage('node')->load($account_target)->getTitle();
        $body = $entity->get('comment_body')->value;
        $node_id =  $entity->getCommentedEntity()->id();

        $params = array(
          'uemail' => $to->getEmail(),
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
        $params['values'] = $params;
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $result = $mailManager->mail($module, $key, $to->getEmail(), $langcode, $params, NULL, $send);
        if ($result['result'] !== true) {
          $messenger = \Drupal::messenger();
          $messenger->addMessage('An error happened and the notification was not sent', $messenger::TYPE_WARNING);
          return FALSE;
        } else {
          return TRUE;
        }

      break;

      case 'tasks':

        //init helper functions from gary_custom
        $helper = new GaryFunctions();

        //the project or opportunity referencing this task
        $task = $entity->getCommentedEntity();
        $task_id =  $entity->getCommentedEntity()->id();
        $task_title = $entity->getCommentedEntity()->getTitle();

        $parent_nid = $helper->getParentNid($task);
        $parent = \Drupal::entityTypeManager()->getStorage('node')->load($parent_nid);
        $parent_title = $parent->title->value;
        $parent_bundle = $parent->bundle();

        $account_target = $parent->get('field_account_reference')->target_id;
        $account_title = \Drupal::entityTypeManager()->getStorage('node')->load($account_target)->getTitle();

        $comment_body = $entity->get('comment_body')->value;
        $comment_id =  $entity->id();


        $params = array(
          'uemail' => $to->getEmail(),
          'poster' => $from->getEmail(),
          'poster_name' => $from->getDisplayName(),
          'body' => $comment_body,
          'bundle' => $host_entity_bundle,
          'parent_bundle'  => $parent_bundle,
          'node_title' => $task_title,
          'node_id' => $task_id,
          'parent_title' => $parent_title,
          'parent_nid' => $parent_nid,
          'account_title' => $account_title,
        );

        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'gary_comments';
        $key = 'comment_task_tag';
        $params['values'] = $params;
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $result = $mailManager->mail($module, $key, $to->getEmail(), $langcode, $params, NULL, $send);
        if ($result['result'] !== true) {
          $messenger = \Drupal::messenger();
          $messenger->addMessage('An error happened and the notification was not sent', $messenger::TYPE_WARNING);
          return FALSE;
        } else {
          return TRUE;
        }


      break;
    }
    return;
  }

  /**
   * Process the body and build an array of tagged users
   * @param string $body The body text
   * @return mixed A mixed array of user objects
   */
  protected function GetTaggedUsers($body) {

    $str = explode(" ",$body);

    $users = [];

    //iterate through comment words and look for a tag
    foreach($str as $k=>$word){
      if(substr($word,0,1)=="@"){
        $uname = trim(substr($word,1));

        $tuser = user_load_by_name($uname);

        if (! $tuser) {
          $usersByNickName = $this->loadUserByTagNickname($uname);

          if ($usersByNickName && property_exists($usersByNickName[0], 'entity_id')) {
            $tuser = User::load($usersByNickName[0]->entity_id);
          }
        }

        if(!empty($tuser)) {
          $users[] = $tuser;
        } else {
          //set an error string if the tagged user returns empty
          \Drupal::logger('commentstag')->debug('error here');
          $this->error_string = t("You tagged @user but I can't find that person!", ['@user' => $uname]);
        }
      }
    }

    //set this for later use
    $this->tagged_users = $users;
    return $users;
  }

  public function loadUserByTagNickname(string $uname)
  {
    $query = \Drupal::database()->select('user__field_tag_nicknames', 'tn');
    $query->addField('tn', 'field_tag_nicknames_value');
    $query->addField('tn', 'entity_id');
    $query->condition('tn.deleted', 0);
    $query->condition('tn.field_tag_nicknames_value', '%'.$uname.'%', 'LIKE');
    return $query->execute()->fetchAll();
  }
}
