<?php
namespace Drupal\rest_oai\Plugin\rest\resource;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "oai_get_resource",
 *   label = @Translation("OAI-mimic Resource"),
 *   uri_paths = {
 *     "canonical" = "/oai"
 *   }
 * )
 */
class OAI_GetResource extends ResourceBase {
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
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    // You must implement the logic of your REST Resource here.
    // for usage without need to authenticate the request, give permission for anonymous users' access...
    // ... to the fucntion "Access GET on OAI-mimic Resource resource"...
    // ... on the Permissions' Configuration Page.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $entities = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['type' => 'item']);
    foreach ($entities as $entity) {
      $result[$entity->id()] = array(
        'dc:title' => $entity->title->value,
        'dc:creator' => $entity->get('field_creator_personal')->value,
        'dc:citation' => $entity->get('field_citation')[0]->value,
        'dcterms:dateIssued' => $entity->get('field_date_issued')->value,
        'dc:language scheme=&quot;ags:ISO639-2&quot;"' => $entity->get('field_language')->value,
        'bitch' => array('tsu:gero' => 'ma pussy pops')
        );
    }
    unset($entity);
    $response = new ResourceResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }
}