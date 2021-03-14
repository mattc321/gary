<?php

namespace Drupal\gary_pdf_templates\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\Renderer;
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

      $template_entity = $this->entityTypeManager->getStorage('node')->load($request->query->get('tid'));
      $entity = $this->entityTypeManager->getStorage('node')->load($request->query->get('eid'));

      $build  = [
        '#theme' => 'pdf_template',
        '#template_entity' => $template_entity,
        '#entity' => $entity
      ];

      $build['#attached']['library'][] = 'gary_pdf_templates/template';

      $path = drupal_get_path(
          'module',
          'gary_pdf_templates'
        ) . '/css/pdf_template.css';
      $build['#css'] = '/'.$path;

      $output = $this->renderer->renderRoot($build);
      $mpdf = new Mpdf(['tempDir' => 'sites/default/files/pdfs/']);
      $mpdf->WriteHTML($output);
      $mpdf->Output($request->query->get('file') . '.pdf', Destination::DOWNLOAD);
      return new Response();
    } catch (\Exception $e) {
      throw new \HttpRequestException('Could not complete request');
    }

  }
}
