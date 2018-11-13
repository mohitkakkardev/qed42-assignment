<?php
/**
*	@file
*	contains Drupal\user_listing\Controller\CompanionProfileData
*/
namespace Drupal\user_listing\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Cache\CacheBackendInterface;
class ListingController extends ControllerBase{
    /**
     * The entity query.
     * 
     * @var \Drupal\Core\Entity\Query\QueryFactory
     */
    protected $entityQuery;
    /**
     * Constructs a \Drupal\user_listing\Controller\ListingController.
     * 
     * @param \Drupal\Core\Entity\Query\QueryFactory         $entity_query
     */
    public function __construct(QueryFactory $entity_query) {
        $this->entityQuery = $entity_query;
    }    
    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
        $container->get('entity.query')
        );
    }
    /**
     * utility: returns the users in user entity
     * 
     * @param birthYear
     * 
     * User Birth Year
     */
	public function content($birthYear = NULL) {
        // return the result in custom theme
        return array(
            '#theme' => 'user_listing',
            '#cache' => ['max-age' => 3600 * 5, 'tags' => ['user.user_list']],
            '#users' => $this->getUsersData($birthYear)
          );
    }
    /**
     * Get users data
     */
    protected function getUsersData($date) 
    {   
        // check if dynamic param is consisting of year or not
        if(is_numeric($date) && strlen($date) == 4){
            // get all the users uid from the database
            $finalResult = array();
            $uids = $this->entityQuery->get('user')
                ->condition('field_date_of_birth', $date.'%', 'LIKE');
            $result = $uids->execute();
            $users = User::loadMultiple($result);
            if($users){
                foreach ($users as $user) {
                    $finalResult[$user->id()] = [
                        'f_name' => $user->field_first_name->getValue()[0]['value'],
                        'l_name' => $user->field_last_name->getValue()[0]['value'],
                        'email' => $user->field_email->getValue()[0]['value'],
                        'phone' => $user->field_phone->getValue()[0]['value'],
                        'dob' => $user->field_date_of_birth->getValue()[0]['value'],
                    ];
                }
            }
        }
        return $finalResult;
    }

}
