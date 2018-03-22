<?php
namespace Drupal\rest_oai\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\file\Entity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "oai_resource",
 *   label = @Translation("OAI: Requests by Verb"),
 *   uri_paths = {
 *     "canonical" = "/oai/{verb}"
 *   }
 * )
 */

class OAI_Resource extends ResourceBase {
  
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  
  protected $currentUser;
  
  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
      parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
      $this->currentUser = $current_user;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest_oai'),
      $container->get('current_user')
    );
  }
  
  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity, or a single node, or even the library's info.
   * Response is dependent of the verb passed
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($verb) {
    // You must implement the logic of your REST Resource here.
    // For usage without need to authenticate the request, give permission for anonymous users' access...
    // ... to the fucntion "Access GET on OAI: Requests by Verb resource"...
    // ... on section "RESTful Web Services"...
    // ... on the PERMISSIONS' Configuration Page.
    
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    switch ($verb) {
      case 'listrecords':
        $response = listRecords();
        return $response;
        break;
      
      default:
        # code...
        break;
    }
  }
}

function listRecords(){
  $entities = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties(['type' => 'item']);
  $result['responseDate'] = date("l, Y-m-d, h:i:s A");
  $result['request verb="ListRecords" set="?????"'] = 'http://solidaridadlibrary.org/oai/';
  foreach ($entities as $entity) {
    $result[$entity->id()] = array(
      'header' => array(
        'identifier' => '',
        'datestamp' => '',
        'setSpec' => ''
      ),
      'metadata' => array(
        'dc:identifier' => array(
          'dc:identifier:number' => $entity->id(),
          'dc:identifier:uri' => \Drupal\rest_oai\Plugin\rest\resource\getFileURI($entity->get('field_digital_document')->value, $entity->get('field_document_upload'), $entity->get('field_document_url')),
          'dc:identifier:citation' => $entity->get('field_citation')->value,
        ),
        'dc:title' => $entity->title->value,
        'dc:creator' => $entity->get('field_creator_personal')->value,
        'dc:citation' => $entity->get('field_citation')->value,
        'dc:contributor' => $entity->get('field_contributor')->value,
        'dc:subject' => \Drupal\rest_oai\Plugin\rest\resource\getReferences($entity->get('field_subject')),
        'dc:coverage' => $entity->get('field_coverage')->value,
        'dc:description' => $entity->get('field_description')->value,
        'dc:type' => $entity->get('field_type')->value,
        'dc:format' => $entity->get('field_format')->value,
        'dc:language scheme="ags:ISO639-2"' => $entity->get('field_language')->value,
        'dc:publisher:place' => $entity->get('field_publisher_place')->value,
        'dc:publisher' => $entity->get('field_publisher')->value,
        'dc:relation' => \Drupal\rest_oai\Plugin\rest\resource\getReferences($entity->get('field_related_reference')),
        'dc:relation:ispartof' => \Drupal\rest_oai\Plugin\rest\resource\getReferences($entity->get('field_related_document')),
        'dc:source' => $entity->get('field_source')->value,
        'dc:rights' => $entity->get('field_rights')->value,
        'dcterms:dateIssued' => $entity->get('field_date_issued')->value,
        )
      );
  }
  unset($entity);
  $response = new ResourceResponse($result);
  $response->addCacheableDependency($result);
  return $response;
}

function listSets(){
  $entities = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties(['type' => 'collection']);
  foreach ($entities as $entity) {

  }
  unset($entity);
  $response = new ResourceResponse($result);
  $response->addCacheableDependency($result);
  return $response;
}

// Getting the listage of references (subject, related references/document, etc.), of the item...
function getReferences($list){
  $terms = array();
  if(!$list->isEmpty()){
    foreach($list as $singleTerm){
      $terms[] = $singleTerm->target_id;
      }
  }
  return implode(", ",$terms);
}

// Getting the URI of the item's file...
function getFileURI($documentType, $caseUpload, $caseExternal){
  if(strcmp($documentType,'upload') == 0){
    return file_create_url($caseUpload->entity->getFileUri());
  }
  else{
    return $caseExternal->uri;
  }
}