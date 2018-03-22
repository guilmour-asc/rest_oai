<?php
namespace Drupal\rest_oai\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\file\Entity;
use Symfony\Component\HttpFoundation\Request;
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
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;
  
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
    AccountProxyInterface $current_user,
    Request $currentRequest) {
      parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
      $this->currentUser = $current_user;
      $this->currentRequest = $currentRequest;
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
      $container->get('current_user'),
      $container->get('request_stack')->getCurrentRequest()
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
    $param_id = $this->currentRequest->get('id');
    $set_id = $this->currentRequest->get('set');

    switch ($verb) {
      case 'identify':
        return identify();
        break;
      
      case 'getrecord':
        return getRecord($param_id);
        break;

      case 'listrecords':
        return listRecords($set_id);
        break;
      
      case 'listsets':
        return listSets();
        break;
      
      case 'listmetadataformats':
        return listMetadataFormats();
        break;
      
      default:
        # code...
        break;
    }
  }
}

// ---------- Verb: IDENTIFY
function identify(){
  $result['responseDate'] = date("Y-m-d\TH:i:s\Z");
  $result['request verb="Identify"'] = 'http://solidaridadlibrary.org/oai/';
  $result['Identify'] = array(
    'repositoryName' => 'Solidaridad Library Open Archive Initiative Repository',
    'baseURL' => 'http://solidaridadlibrary.org/oai/',
    'protocolVersion' => '2.0',
    'adminEmail' => 'contact@solidaridadlibrary.org',
    'earliestDatestamp' => '',
    'deletedRecord' => 'no',
    'granularity' => 'YYYY-MM-DDThh:mm:ssZ'
  );
  $response = new ResourceResponse($result);
  $response->addCacheableDependency($result);
  unset($result);
  return $response;
}
// -------------------------

// ---------- Verb: GETRECORD
function getRecord($param_id){
  if(!$param_id){
    $result['responseDate'] = date("Y-m-d\TH:i:s\Z");
    $result['request verb="GetRecord" id="'.$param_id.'"'] = 'http://solidaridadlibrary.org/oai/';
    $result['error'] = 'Verb Error: the id was not specified as a parameter, on the verb\'s calling.';
    $response = new ResourceResponse($result);
    $response->addCacheableDependency($result);
    unset($result);
    return $response;
  }
  else{
    $entity = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->load($param_id);
    $result['responseDate'] = date("Y-m-d\TH:i:s\Z");
    $result['request verb="GetRecord" id="'.$param_id.'"'] = 'http://solidaridadlibrary.org/oai/';
    $result['record'] = array(
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
        'dcterms:dateIssued' => $entity->get('field_date_issued')->value
        )
      );
    unset($entities);
    $response = new ResourceResponse($result);
    $response->addCacheableDependency($result);
    unset($result);
    return $response;
  }
}
// --------------------------

// ---------- Verb: LISTRECORDS
function listRecords($set_id){
  $entities = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties(['type' => 'item']);
  $result['responseDate'] = date("Y-m-d\TH:i:s\Z");
  if(!$set_id){
    $result['request verb="ListRecords"'] = 'http://solidaridadlibrary.org/oai/';
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
          'dcterms:dateIssued' => $entity->get('field_date_issued')->value
          )
        );
    }
    unset($entities);
    $response = new ResourceResponse($result);
    $response->addCacheableDependency($result);
    unset($result);
    return $response;
  }
  else{
    $result['request verb="ListRecords" set="'.$set_id.'"'] = 'http://solidaridadlibrary.org/oai/';    
    foreach ($entities as $entity) {
      if(stripos(\Drupal\rest_oai\Plugin\rest\resource\getReferences($entity->get('field_related_reference')), strval($set_id)) !== false){
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
            'dcterms:dateIssued' => $entity->get('field_date_issued')->value
            )
          );
        }
    }
    unset($entities);
    $response = new ResourceResponse($result);
    $response->addCacheableDependency($result);
    unset($result);
    return $response;
  }
}
// ----------------------------

// ---------- Verb: LISTSETS
function listSets(){
  $entities = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties(['type' => 'collection']);
  $result['responseDate'] = date("Y-m-d\TH:i:s\Z");
  $result['request verb="ListSets"'] = 'http://solidaridadlibrary.org/oai/';
  foreach ($entities as $entity) {
    $result[$entity->id()] = array(
      'header' => array(
        'identifier' => '',
        'datestamp' => '',
        'setSpec' => ''
      ),
      'metadata' => array(
        'dc:identifier' => array(
          'dc:identifier:number' => $entity->id()
        ),
        'dc:type' => 'Collection',
        'dc:title' => $entity->title->value,
        'dc:language scheme="ags:ISO639-2"' => $entity->get('field_language_c')->value,
        'dcterms:alternative' => \Drupal\rest_oai\Plugin\rest\resource\getDoubleFields($entity->get('field_translated_title')),
        'dcterms:abstract' => $entity->get('field_abstract')->value,
        'cld:itemType' => $entity->get('field_type_c')->value,
        'dc:subject' => \Drupal\rest_oai\Plugin\rest\resource\getReferences($entity->get('field_commodities')),
        'dcterms:spatial' => \Drupal\rest_oai\Plugin\rest\resource\getReferences($entity->get('field_countries'))
        // 'data' => $entity->getFields()
        )
    );
  }
  unset($entity);
  $response = new ResourceResponse($result);
  $response->addCacheableDependency($result);
  unset($result);
  return $response;
}
// -------------------------

// ---------- Verb: LISTMETADATAFORMATS
function listMetadataFormats(){
  $result['responseDate'] = date("Y-m-d\TH:i:s\Z");
  $result['request verb="ListMetadataFormats"'] = 'http://solidaridadlibrary.org/oai/';
  $result['ListMetadataFormats'] = array(
    'metadataFormat' => array(
      'metadataPrefix' => 'oai_dc',
      'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
      'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/'
      )
  );
  $response = new ResourceResponse($result);
  $response->addCacheableDependency($result);
  unset($result);
  return $response;
}
// ------------------------------------

// GETTING THE LISTAGE OF REFERENCES (SUBJECT, RELATED REFERENCES/DOCUMENT, ETC.), OF THE ITEM...
function getReferences($list){
  $terms = array();
  if(!$list->isEmpty()){
    foreach($list as $singleTerm){
      $terms[] = $singleTerm->target_id;
      }
  }
  return implode(", ",$terms);
}

// GETTING THE LISTAGE OF DOUBLE-FIELDS...
function getDoubleFields($list){
  $terms = array();
  if(!$list->isEmpty()){
    foreach($list as $singleTerm){
      $terms[] = $singleTerm->first.': '.$singleTerm->second;
      }
  }
  return implode("; ",$terms);
}

// GETTING THE URI OF THE ITEM'S FILE...
function getFileURI($documentType, $caseUpload, $caseExternal){
  if(strcmp($documentType,'upload') == 0){
    return file_create_url($caseUpload->entity->getFileUri());
  }
  else{
    return $caseExternal->uri;
  }
}