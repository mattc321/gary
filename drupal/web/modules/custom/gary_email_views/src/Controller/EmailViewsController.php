<?php
/**
 * @file
 * Contains \Drupal\gary_email_views\Controller\EmailViewsController.
 */
namespace Drupal\gary_email_views\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;

class EmailViewsController extends ControllerBase {

  public function sendEmail() {
    return true;
  }

/**
  * The form builder.
  *
  * @var \Drupal\Core\Form\FormBuilder
  */
 protected $formBuilder;

 /**
  * The ModalFormExampleController constructor.
  *
  * @param \Drupal\Core\Form\FormBuilder $formBuilder
  *   The form builder.
  */
 public function __construct(FormBuilder $formBuilder) {
   $this->formBuilder = $formBuilder;
 }

 /**
  * {@inheritdoc}
  *
  * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
  *   The Drupal service container.
  *
  * @return static
  */
 public static function create(ContainerInterface $container) {
   return new static(
     $container->get('form_builder')
   );
 }

 /**
  * Callback for opening the modal form.
  */
 public function openModalForm() {
   $response = new AjaxResponse();
   $modal_form = $this->formBuilder->getForm('Drupal\gary_email_views\Form\PopupEmail');

   $response->addCommand(new OpenModalDialogCommand('Send Email', $modal_form, ['width' => '800']));

   return $response;
 }


}
