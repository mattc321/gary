<?php

namespace Drupal\gary_pdf_templates\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\Renderer;
use Drupal\gary_custom\GaryFunctions;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class PdfGenerate extends ControllerBase
{
  /**
   * @var Renderer
   */
  private $renderer;

  /**
   * PdfGenerate constructor.
   * @param EntityTypeManager $entityTypeManager
   * @param Messenger $messenger
   * @param Renderer $renderer
   */
  public function __construct(EntityTypeManager $entityTypeManager, Messenger $messenger, Renderer $renderer)
  {
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->renderer = $renderer;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('renderer')
    );
  }

  public function renderPdf(Request $request)
  {

    if (! $request->query->all()) {
      throw new MissingMandatoryParametersException();
    }

    try {

      $params = [
        'tid',
        'eid',
        'file',
      ];

      foreach ($params as $param) {
        if (! $request->query->get($param)) {
          throw new MissingMandatoryParametersException();
        }
      }

      /** @var Node $template_entity */
      $template_entity = $this->entityTypeManager->getStorage('node')->load($request->query->get('tid'));

      /** @var Node $entity */
      $entity = $this->entityTypeManager->getStorage('node')->load($request->query->get('eid'));

      $build  = [
        '#theme' => 'pdf_template',
        '#template_entity' => $template_entity,
        '#entity' => $entity,
        '#additional_vars' => [
          'primary_contact' => $this->getPrimaryContact($entity)
        ],
      ];

      $build['#attached']['library'][] = 'gary_pdf_templates/template';

      //css
      $path = drupal_get_path(
          'module',
          'gary_pdf_templates'
        ) . '/css/pdf-template.css';
      $build['#css'] = '/'.$path;

      //font
      $fontPath = drupal_get_path(
          'module',
          'gary_pdf_templates'
        ) . '/fonts/';

      $output = $this->renderer->renderRoot($build);

      $config = [
        'fontDir' => [
          $fontPath
        ],
        'fontdata' => [
            'montserrat' => [
              'R' => 'Montserrat-Regular.ttf',
              'I' => 'Montserrat-Medium.ttf',
            ]
          ],
        'default_font' => 'montserrat',
        'tempDir' => 'sites/default/files/pdfs/'
      ];

      $mpdf = new Mpdf($config);
      $mpdf->setHtmlHeader($template_entity->field_page_header->value);
      $mpdf->setHtmlFooter($template_entity->field_page_footer->value);
      $mpdf->WriteHTML($output);
      $mpdf->Output($request->query->get('file') . '.pdf', Destination::DOWNLOAD);
      return new Response();
    } catch (\Exception $e) {
      throw new \HttpRequestException('Could not complete request');
    }

  }

  private function getPrimaryContact(Node $entity)
  {
    if (!$entity->hasField('field_project_contacts')) {
      return '';
    }

    $helper = new GaryFunctions();
    $first = null;
    foreach ($entity->get('field_project_contacts') as $index => $paragraphItem) {

      $contactPgId = $paragraphItem->getValue();
      $contact = Paragraph::load(array_shift($contactPgId));
      $first = $index === 0 ? $contact : $first;

      if ($contact->field_primary->value == 1) {
        return $helper::loadEntityRef($contact, 'field_contact_reference');
      }
    }

    if ($first) {
      return $helper::loadEntityRef($first, 'field_contact_reference');
    }

    return '';

  }

}
