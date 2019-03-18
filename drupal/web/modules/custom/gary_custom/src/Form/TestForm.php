<?php


namespace Drupal\gary_custom\Form;

use Drupal\Component\Utility\Random;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\homebox\Entity\HomeboxInterface;
use Drupal\homebox\Entity\HomeboxLayout;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerInterface;


class TestForm extends FormBase {

  public function getFormId() {
    return 'fuckshit';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
      $form['poop'] = [
        '#markup' => 'tetsy'
      ];
      $block_instance = \Drupal::service('plugin.manager.block')->createInstance("views_block:dashboard_tasks_by_assignee-block_1");
      $form['dank'] = $block_instance->build();
      // $form['test'] = [
      //   '#view' => views_embed_view('dashboard_tasks_by_assignee', 'block_1')
      // ];

      // $view = \Drupal\views\Views::getView('dashboard_tasks_by_assignee');
      // $view->setDisplay('block_1');
      // $title = $view->getTitle();
      // $render = $view->render();
      // $form['dang'] = $render;
      return $form;

  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
